<?php

/*
 * @package    Backup
 * @author     BloodhunterD <bloodhunterd@bloodhunterd.com>
 * @link       https://github.com/bloodhunterd
 * @copyright  © 2020 BloodhunterD
 */

declare(strict_types=1);

namespace Backup\Agent\Service\Database;

use Backup\Agent\Service\DatabaseService;
use Backup\Exception\DatabaseException;
use Backup\Exception\ToolException;
use Backup\Logger;
use Backup\Agent\Model\DatabaseModel;
use Backup\Tool;
use Vection\Component\DI\Annotations\Inject;
use Vection\Component\DI\Traits\AnnotationInjection;
use function in_array;

/**
 * Class MySqlService
 *
 * @package Backup\Agent\Service\Database
 *
 * @author BloodhunterD
 */
class MySqlService
{
    use AnnotationInjection;

    private const ENV = '\$';
    private const PASSWORDS = ['MYSQL_ROOT_PASSWORD', 'MYSQL_PASSWORD'];
    private const NO_PASSWORD = 'MYSQL_ALLOW_EMPTY_PASSWORD';
    private const EXCLUDED_SCHEMATA = ['information_schema', 'mysql', 'performance_schema', 'sys'];

    /**
     * @var DatabaseModel
     */
    private $database;

    /**
     * @var Logger
     * @Inject("Backup\Logger")
     */
    private $logger;

    /**
     * @var Tool
     * @Inject("Backup\Tool")
     */
    private $tool;

    /**
     * Backup a database
     *
     * @param DatabaseModel $database
     *
     * @throws DatabaseException
     */
    public function backupDatabase(DatabaseModel $database): void
    {
        $this->database = $database;

        try {
            $this->tool->createDirectory($this->database->getTarget());
        } catch (ToolException $e) {
            $msg = sprintf('Failed to create target directory for database backup "%s".', $this->database->getName());

            throw new DatabaseException($msg, 0, $e);
        }

        if ($this->database->getType() === DatabaseService::TYPE_DOCKER) {
            $cmd = $this->getDockerCommand($this->getSchemataQuery());
        } else {
            $cmd = $this->getHostCommand($this->getSchemataQuery());
        }

        # Get all available database schemata
        try {
            $schemata = $this->tool->execute($cmd);
        } catch (ToolException $e) {
            $msg = sprintf('Failed to get schemata for database backup "%s".', $this->database->getName());

            throw new DatabaseException($msg, 0, $e);
        }

        $schemata = explode(' ', $schemata[0]);

        foreach ($schemata as $schema) {
            try {
                $this->backupSchema($schema);
            } catch (DatabaseException $e) {
                $this->logger->use('app')->error($e->getMessage(), [
                    'previous' => $e->getPrevious()->getMessage()
                ]);
            }
        }
    }

    /**
     * Prepare host command
     *
     * @param string $query
     * @return string
     */
    private function getHostCommand(string $query): string
    {
        $cmd = 'mysql%s%s%s --skip-column-names -e \'%s;\'';

        return sprintf($cmd, $this->prepareHost(), $this->prepareUser(), $this->preparePassword(), $query);
    }

    /**
     * Prepare Docker command
     *
     * @param string $query
     * @return string
     */
    private function getDockerCommand(string $query): string
    {
        $cmd = 'docker exec %s sh -c "mysql%s%s --skip-column-names -e \'%s;\'"';

        return sprintf(
            $cmd,
            $this->database->getDockerContainer(),
            $this->prepareUser(),
            $this->preparePassword(),
            $query
        );
    }

    /**
     * Prepare host
     *
     * @return string
     */
    private function prepareHost(): string
    {
        $host = $this->database->getHost();

        return $host ? sprintf(' -h%s', escapeshellarg($host)) : '';
    }

    /**
     * Prepare user
     *
     * @return string
     */
    private function prepareUser(): string
    {
        $user = $this->database->getUser();

        # Replace Docker Compose environment vars
        if ($this->database->getType() === DatabaseService::TYPE_DOCKER && strncasecmp($user, 'MYSQL_USER', 10) === 0) {
            $user = self::ENV . $user;
        } else {
            $user = escapeshellarg($user);
        }

        return $user ? sprintf(' -u%s', $user) : '';
    }

    /**
     * Prepare password
     *
     * @return string
     */
    private function preparePassword(): string
    {
        $password = $this->database->getPassword();

        if ($this->database->getType() === DatabaseService::TYPE_DOCKER) {
            # Handle Docker Compose environment vars
            if (in_array($password, self::PASSWORDS, true)) {
                $password = self::ENV . $password;
            } else if ($password === self::NO_PASSWORD) {
                $password = false;
            } else {
                $password = $password ? escapeshellarg($password) : false;
            }
        } else {
            $password = $password ? escapeshellarg($password) : false;
        }

        return $password ? sprintf(' -p%s', $password) : '';
    }

    /**
     * Get schemata query
     *
     * @return string
     */
    private function getSchemataQuery(): string
    {
        $query = 'SELECT
                    GROUP_CONCAT(schema_name SEPARATOR \" \")
                  FROM
                    information_schema.schemata
                  WHERE
                    schema_name NOT IN (\"%s\")
                  ';

        $query = sprintf($query, implode('\",\"', self::EXCLUDED_SCHEMATA));

        return $query;
    }

    /**
     * Backup schema
     *
     * @param string $schema
     * @throws DatabaseException
     */
    private function backupSchema(string $schema): void
    {
        $name = $this->database->getName();

        $this->database->setSource($this->tool::sanitize($name) . '.' . $schema . '.sql');

        try {
            $this->tool->execute($this->getSchemaDumpCommand($schema));
        } catch (ToolException $e) {
            $msg = sprintf('Failed to create dump for schema "%s" of database backup "%s".', $schema, $name);

            throw new DatabaseException($msg, 0, $e);
        }

        $this->database->setArchive($this->tool::sanitize($name) . '.' . $schema);

        try {
            $this->tool->createArchive($this->database);
        } catch (ToolException $e) {
            $msg = sprintf('Failed to create archive for schema %s of database backup "%s".', $schema, $name);

            throw new DatabaseException($msg, 0, $e);
        }
    }

    /**
     * Get schema dump command
     *
     * @param string $schema
     * @return string
     */
    private function getSchemaDumpCommand(string $schema): string
    {
        return $this->database->getType() === DatabaseService::TYPE_DOCKER ? $this->getDockerDumpCommand($schema) : $this->getHostDumpCommand($schema);
    }

    /**
     * Get docker dump command
     *
     * @param string $schema
     * @return string
     */
    private function getDockerDumpCommand(string $schema): string
    {
        return sprintf(
            'docker exec %s sh -c "mysqldump%s%s %s" > %s',
            escapeshellarg($this->database->getDockerContainer()),
            $this->prepareUser(),
            $this->preparePassword(),
            escapeshellarg($schema),
            escapeshellarg($this->database->getSource())
        );
    }

    /**
     * Get host dump command
     *
     * @param string $schema
     * @return string
     */
    private function getHostDumpCommand(string $schema): string
    {
        return sprintf(
            'mysqldump%s%s%s %s > %s',
            $this->prepareHost(),
            $this->prepareUser(),
            $this->preparePassword(),
            escapeshellarg($schema),
            escapeshellarg($this->database->getSource())
        );
    }
}
