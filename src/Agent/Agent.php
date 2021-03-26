<?php
/*
 * This file ist part of the Backup project, see https://github.com/bloodhunterd/Backup.
 * © 2021 BloodhunterD <bloodhunterd@bloodhunterd.com>
 */

declare(strict_types=1);

namespace Backup\Agent;

use Backup\Agent\Service\Database\MongoDbService;
use Backup\Agent\Service\Database\MySqlService;
use Backup\Agent\Service\Database\PostgresService;
use Backup\Configuration;
use Backup\Exception\DatabaseException;
use Backup\Exception\DirectoryException;
use Backup\Exception\ToolException;
use Backup\Interfaces\Backup;
use Backup\Agent\Model\DatabaseModel;
use Backup\Agent\Model\DirectoryModel;
use Backup\Agent\Service\DatabaseService;
use Monolog\Logger;
use Backup\Report\Report;
use Backup\Tool;
use Vection\Component\DI\Annotations\Inject;
use Vection\Component\DI\Traits\AnnotationInjection;

/**
 * Class Agent
 *
 * @package Backup\Agent
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class Agent implements Backup
{
    use AnnotationInjection;

    /**
     * @var Configuration
     * @Inject("Backup\Configuration")
     */
    private Configuration $config;

    /**
     * @var Logger
     * @Inject("Monolog\Logger")
     */
    private Logger $logger;

    /**
     * @var Tool
     * @Inject("Backup\Tool")
     */
    private Tool $tool;

    /**
     * @var Report
     * @Inject("Backup\Report\Report")
     */
    private Report $report;

    /**
     * @var MongoDbService
     * @Inject("Backup\Agent\Service\Database\MongoDbService")
     */
    private MongoDbService $mongoDbService;

    /**
     * @var MySqlService
     * @Inject("Backup\Agent\Service\Database\MySqlService")
     */
    private MySqlService $mySqlService;

    /**
     * @var PostgresService
     * @Inject("Backup\Agent\Service\Database\PostgresService")
     */
    private PostgresService $postgresService;

    /**
     * @inheritDoc
     */
    public function run(): void
    {
        $directories = $this->config->getDirectories();

        if (!$directories) {
            $this->logger->info('No directories set in configuration.');
        }

        foreach ($directories as $directory) {
            $directoryModel = new DirectoryModel($directory);

            if ($directoryModel->isDisabled()) {
                $this->logger->info(
                    sprintf('Backup of directory "%s" is disabled.', $directoryModel->getName())
                );

                $this->report->add(
                    Report::RESULT_INFO,
                    self::TYPE_DIRECTORY,
                    'Backup disabled.',
                    $directoryModel
                );

                continue;
            }

            try {
                $this->tool->setDurationStart();

                $this->backupDirectory($directoryModel);

                $duration = $this->tool->getDuration();
            } catch (DirectoryException $e) {
                $this->logger->error($e->getMessage());
                $this->logger->debug($e->getTraceAsString());

                $this->report->add(
                    Report::RESULT_ERROR,
                    self::TYPE_DIRECTORY,
                    $e->getMessage(),
                    $directoryModel
                );

                continue;
            }

            $fileSize = filesize(
                $this->config->getTargetDirectory() .
                $directoryModel->getTarget() .
                DIRECTORY_SEPARATOR .
                $directoryModel->getArchive()
            );

            $this->report->add(
                Report::RESULT_OK,
                self::TYPE_DIRECTORY,
                'Files archived.',
                $directoryModel,
                $fileSize ?: null,
                $duration
            );
        }

        $databases = $this->config->getDatabases();

        if (!$databases) {
            $this->logger->info('No databases set in configuration.');
        }

        foreach ($databases as $database) {
            $databaseModel = new DatabaseModel($database);

            if ($databaseModel->isDisabled()) {
                $this->logger->info(
                    sprintf('Backup of database "%s" is disabled.', $databaseModel->getName())
                );

                $this->report->add(
                    Report::RESULT_INFO,
                    self::TYPE_DATABASE,
                    'Backup disabled.',
                    $databaseModel
                );

                continue;
            }

            try {
                $this->tool->setDurationStart();

                switch ($databaseModel->getSystem()) {
                    case DatabaseService::SYSTEM_POSTGRES:
                        $this->postgresService->backupDatabase($databaseModel);
                        break;
                    case DatabaseService::SYSTEM_MONGODB:
                        $this->mongoDbService->backupDatabase($databaseModel);
                        break;
                    case DatabaseService::SYSTEM_MARIADB:
                    case DatabaseService::SYSTEM_MYSQL:
                    default:
                        $this->mySqlService->backupDatabase($databaseModel);
                }

                $duration = $this->tool->getDuration();
            } catch (DatabaseException $e) {
                $this->logger->error($e->getMessage());
                $this->logger->debug($e->getTraceAsString());

                $this->report->add(
                    Report::RESULT_ERROR,
                    self::TYPE_DATABASE,
                    $e->getMessage(),
                    $databaseModel
                );

                continue;
            }

            $fileSize = filesize(
                $this->config->getTargetDirectory() .
                $databaseModel->getTarget() .
                DIRECTORY_SEPARATOR .
                $databaseModel->getArchive()
            );

            $this->report->add(
                Report::RESULT_OK,
                self::TYPE_DATABASE,
                'Files archived.',
                $databaseModel,
                $fileSize ?: null,
                $duration
            );
        }

        // Send report
        if ($this->config->isReportEnabled()) {
            if ($this->report->send()) {
                $this->logger->info('Report sent.');
            } else {
                $this->logger->error('Failed to sent report.');
            }
        }
    }

    /**
     * Backup a directory
     *
     * @param DirectoryModel $directory
     *
     * @throws DirectoryException|ToolException
     */
    public function backupDirectory(DirectoryModel $directory): void
    {
        $name = $directory->getName();

        if (!$this->tool->createDirectory($directory->getTarget())) {
            $msg = sprintf('Failed to create target directory for directory "%s".', $name);

            throw new DirectoryException($msg);
        }

        $this->logger->info(sprintf('Target directory "%s" created.', $name));

        $directory->setArchive(Tool::sanitize($name) . '.tar.' . $this->tool->getArchiveSuffix());

        $cmdBefore = $directory->getCommandBefore();
        if ($cmdBefore) {
            try {
                $this->tool->execute($cmdBefore);
            } catch (ToolException $e) {
                $msg = sprintf('Failed to execute command "%s".', 'BEFORE');

                throw new DirectoryException($msg, 0, $e);
            }

            $this->logger->info(sprintf('Command "%s" was executed.', 'BEFORE'));
        }

        $this->tool->createArchive($directory);

        $cmdAfter = $directory->getCommandAfter();
        if ($cmdAfter) {
            try {
                $this->tool->execute($cmdAfter);
            } catch (ToolException $e) {
                $msg = sprintf('Failed to execute command "%s".', 'AFTER');

                throw new DirectoryException($msg, 0, $e);
            }

            $this->logger->info(sprintf('Command "%s" was executed.', 'AFTER'));
        }
    }
}
