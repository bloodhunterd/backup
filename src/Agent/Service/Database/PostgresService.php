<?php
/*
 * @package    Backup
 * @author     BloodhunterD <bloodhunterd@bloodhunterd.com>
 * @link       https://github.com/bloodhunterd/backup
 * @copyright  © 2021 BloodhunterD
 */

declare(strict_types=1);

namespace Backup\Agent\Service\Database;

use Backup\Agent\Service\DatabaseService;
use Backup\Configuration;
use Backup\Exception\DatabaseException;
use Backup\Exception\ToolException;
use Backup\Agent\Model\DatabaseModel;
use Backup\Tool;
use Vection\Component\DI\Annotations\Inject;
use Vection\Component\DI\Traits\AnnotationInjection;

/**
 * Class PostgresService
 *
 * @package Backup\Agent\Service\Database
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class PostgresService extends DatabaseService
{
    use AnnotationInjection;

    protected const DEFAULT_USER = 'postgres';
    protected const ENV = '$';
    protected const USER = 'POSTGRES_USER';

    /**
     * @var Configuration
     * @Inject("Backup\Configuration")
     */
    private Configuration $config;

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

        try {
            $this->tool->createDirectory($this->database->getTarget());
        } catch (ToolException $e) {
            $msg = sprintf('Failed to create target directory for database backup "%s".', $name);

            throw new DatabaseException($msg, 0, $e);
        }

        $this->database->setSource($this->tool::sanitize($name) . '.sql');

        $cmd = $isDocker ? $this->prepareDockerCommand($this->getCommand()) : $this->getCommand();
        $cmd .= sprintf(
            ' > %s',
            escapeshellarg($this->database->getSource())
        );

        $this->database->setArchive(Tool::sanitize($name) . '.sql.' . $this->tool->getArchiveSuffix());

        try {
            $this->tool->execute($cmd);
        } catch (ToolException $e) {
            $msg = sprintf('Failed to create archive of database backup "%s".', $name);

            throw new DatabaseException($msg, 0, $e);
        }

        $this->tool->createArchive($this->database);
    }

    /**
     * Prepare command
     *
     * @return string
     */
    private function getCommand(): string
    {
        $cmd = 'pg_dumpall%s%s';

        return sprintf($cmd, $this->prepareHost(), sprintf(' -U %s', $this->prepareUser()));
    }
}
