<?php
/*
 * This file ist part of the Backup project, see https://github.com/bloodhunterd/Backup.
 * © 2021 BloodhunterD <bloodhunterd@bloodhunterd.com>
 */

declare(strict_types=1);

namespace Backup\Agent\Service\Database;

use Backup\Agent\Service\DatabaseService;
use Backup\Exception\DatabaseException;
use Backup\Exception\ToolException;
use Backup\Agent\Model\DatabaseModel;
use Backup\Tool;
use function in_array;

/**
 * Class MySqlService
 *
 * @package Backup\Agent\Service\Database
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class MySqlService extends DatabaseService
{
    protected const DEFAULT_USER = 'root';
    protected const ENV = '\$';
    protected const USER = 'MYSQL_USER';

    private const EXCLUDED_SCHEMATA = ['information_schema', 'performance_schema', 'sys'];
    private const NO_PASSWORD = 'MYSQL_ALLOW_EMPTY_PASSWORD';
    private const PASSWORDS = ['MYSQL_ROOT_PASSWORD', 'MYSQL_PASSWORD'];

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

        $name = $this->database->getName();
        $isDocker = $this->database->getType() === DatabaseService::TYPE_DOCKER;

        if (!$this->tool->createDirectory($this->database->getTarget())) {
            $msg = sprintf('Failed to create target directory for database backup "%s".', $name);

            throw new DatabaseException($msg);
        }

        $this->logger->info(sprintf('Target directory "%s" created.', $name));

        if ($isDocker) {
            $cmd = $this->prepareDockerCommand($this->getCommand($this->getSchemataQuery()));
        } else {
            $cmd = $this->getCommand($this->getSchemataQuery());
        }

        # Get all available database schemata
        try {
            $schemata = $this->tool->execute($cmd);
        } catch (ToolException $e) {
            $msg = sprintf('Failed to get schemata for database backup "%s".', $name);

            throw new DatabaseException($msg, 0, $e);
        }

        $schemata = explode(' ', $schemata[0]);

        foreach ($schemata as $schema) {
            try {
                $this->backupSchema($schema);
            } catch (DatabaseException $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }

    /**
     * Prepare command
     *
     * @param string $query
     * @return string
     */
    private function getCommand(string $query): string
    {
        $cmd = 'mysql%s%s%s --skip-column-names -e \'%s;\'';

        return sprintf($cmd, $this->prepareHost(), $this->getUser(), $this->preparePassword(), $query);
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
        $query = <<<sql
            SELECT
                GROUP_CONCAT(schema_name SEPARATOR %s)
            FROM
                information_schema.schemata
            WHERE
                schema_name NOT IN (%s)
        sql;

        return sprintf($query, '\" \"', '\"' . implode('\",\"', self::EXCLUDED_SCHEMATA) . '\"');
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

        try {
            $this->database->setSource($this->tool::sanitize($name) . '.' . $schema . '.sql');

            $cmd = sprintf(
                'mysqldump%s%s%s %s',
                $this->prepareHost(),
                $this->getUser(),
                $this->preparePassword(),
                escapeshellarg($schema)
            );

            $cmd = $this->database->getType() === DatabaseService::TYPE_DOCKER ? $this->prepareDockerCommand($cmd) : $cmd;
            $cmd .= sprintf(
                ' > %s',
                escapeshellarg($this->database->getSource())
            );

            $this->tool->execute($cmd);

            $this->database->setArchive(Tool::sanitize($name) . '_' . $schema . '.sql.' . $this->tool->getArchiveSuffix());
        } catch (ToolException $e) {
            $msg = sprintf('Failed to create dump for schema "%s" of database backup "%s".', $schema, $name);

            throw new DatabaseException($msg, 0, $e);
        }

        $this->tool->createArchive($this->database);
    }

    /**
     * Get user
     *
     * @return string
     */
    private function getUser(): string
    {
        return sprintf(' -u %s', $this->prepareUser());
    }
}
