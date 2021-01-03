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

/**
 * Class MongoDbService
 *
 * @package Backup\Agent\Service\Database
 *
 * @author BloodhunterD
 */
class MongoDbService
{
    use AnnotationInjection;

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
        $name = $database->getName();
        $isDocker = $database->getType() === DatabaseService::TYPE_DOCKER;

        try {
            $this->tool->createDirectory($database->getTarget());
        } catch (ToolException $e) {
            $msg = sprintf('Failed to create target directory for database backup "%s".', $name);

            throw new DatabaseException($msg, 0, $e);
        }

        $database->setArchive($this->tool::sanitize($name) . '.mongo.gz');

        $cmd = 'mongodump --gzip --archive=-';
        if ($isDocker) {
            $cmd = sprintf('docker exec %s sh -c "%s"', $database->getDockerContainer(), $cmd);
        }
        $cmd .= sprintf(' > %s', $database->getTarget() . DIRECTORY_SEPARATOR . $database->getArchive());

        try {
            $this->tool->execute($cmd);
        } catch (ToolException $e) {
            $msg = sprintf('Failed to create archive of database backup "%s".', $name);

            throw new DatabaseException($msg, 0, $e);
        }
    }
}
