<?php
/*
 * This file ist part of the Backup project, see https://github.com/bloodhunterd/Backup.
 * © 2021 BloodhunterD <bloodhunterd@bloodhunterd.com>
 */

declare(strict_types=1);

namespace Backup\Agent\Service\Database;

use Backup\Agent\Service\DatabaseService;
use Backup\Configuration;
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
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class MongoDbService
{
    use AnnotationInjection;

    /**
     * @var Logger
     * @Inject("Backup\Logger")
     */
    private Logger $logger;

    /**
     * @var Configuration
     * @Inject("Backup\Configuration")
     */
    private Configuration $config;

    /**
     * @var Tool
     * @Inject("Backup\Tool")
     */
    private Tool $tool;

    /**
     * Backup a database
     *
     * @param DatabaseModel $database
     * @throws DatabaseException
     */
    public function backupDatabase(DatabaseModel $database): void
    {
        $name = $database->getName();
        $isDocker = $database->getType() === DatabaseService::TYPE_DOCKER;

        if (!$this->tool->createDirectory($database->getTarget())) {
            $msg = sprintf('Failed to create target directory for database backup "%s".', $name);

            throw new DatabaseException($msg);
        }

        $this->logger->use('app')->info(sprintf('Target directory "%s" created.', $name));

        try {
            $database->setArchive($this->tool::sanitize($name) . '.mongo.gz');
        } catch (ToolException $e) {
            throw new DatabaseException($e->getMessage(), 0, $e);
        }

        $cmd = 'mongodump --gzip --archive=-';
        if ($isDocker) {
            $cmd = sprintf('docker exec %s sh -c "%s"', $database->getDockerContainer(), $cmd);
        }
        $cmd .= sprintf(' > %s', $this->config->getTargetDirectory() . $database->getTarget() . DIRECTORY_SEPARATOR . $database->getArchive());

        try {
            $this->tool->execute($cmd);
        } catch (ToolException $e) {
            $msg = sprintf('Failed to create archive of database backup "%s".', $name);

            throw new DatabaseException($msg, 0, $e);
        }
    }
}
