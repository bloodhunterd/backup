<?php

/**
 * This file is part of the Backup project.
 * Visit project at https://github.com/bloodhunterd/backup
 *
 * Copyright © 2019 BloodhunterD <bloodhunterd@bloodhunterd.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Backup\Agent;

use Backup\Configuration;
use Backup\Exception\DatabaseException;
use Backup\Exception\DirectoryException;
use Backup\Exception\ToolException;
use Backup\Interfaces\Backup;
use Backup\Agent\Model\DatabaseModel;
use Backup\Agent\Model\DirectoryModel;
use Backup\Agent\Service\DatabaseService;
use Backup\Logger;
use Backup\Report\Report;
use Backup\Tool;
use PharException;
use Vection\Component\DI\Annotations\Inject;
use Vection\Component\DI\Traits\AnnotationInjection;

/**
 * Class Agent
 *
 * @package Backup\Agent
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class Agent implements Backup
{
    use AnnotationInjection;

    /**
     * @var Configuration
     * @Inject("Backup\Configuration")
     */
    private $config;

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
     * @var Report
     * @Inject("Backup\Report\Report")
     */
    private $report;

    /**
     * @var DatabaseService
     * @Inject("Backup\Agent\Service\DatabaseService")
     */
    private $databaseService;

    /**
     * @inheritDoc
     */
    public function run(): void
    {
        $directories = $this->config->getDirectories();

        if (!$directories) {
            $this->logger->use('app')->warning('No directories set in configuration.');
        }

        foreach ($directories as $directory) {
            $directoryModel = new DirectoryModel($directory);

            if ($directoryModel->isDisabled()) {
                $this->logger->use('app')->info(
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
                $this->logger->use('app')->error($e->getMessage(), [
                    'previous' => $e->getPrevious()->getMessage()
                ]);
                $this->logger->use('app')->debug($e->getTraceAsString());

                $this->report->add(
                    Report::RESULT_ERROR,
                    self::TYPE_DIRECTORY,
                    $e->getPrevious()->getMessage(),
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
            $this->logger->use('app')->warning('No databases set in configuration.');
        }

        foreach ($databases as $database) {
            $databaseModel = new DatabaseModel($database);

            if ($databaseModel->isDisabled()) {
                $this->logger->use('app')->info(
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

                $this->databaseService->backupDatabase($databaseModel);

                $duration = $this->tool->getDuration();
            } catch (DatabaseException $e) {
                $this->logger->use('app')->error($e->getMessage(), [
                    'previous' => $e->getPrevious()->getMessage()
                ]);
                $this->logger->use('app')->debug($e->getTraceAsString());

                $this->report->add(
                    Report::RESULT_ERROR,
                    self::TYPE_DATABASE,
                    $e->getPrevious()->getMessage(),
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
                $this->logger->use('app')->info('Report sent.');
            } else {
                $this->logger->use('app')->error('Failed to sent report.');
            }
        }
    }

    /**
     * Backup a directory
     *
     * @param DirectoryModel $directory
     *
     * @throws DirectoryException
     */
    public function backupDirectory(DirectoryModel $directory): void
    {
        $name = $directory->getName();

        try {
            $this->tool->createDirectory($directory->getTarget());
        } catch (ToolException $e) {
            $msg = sprintf('Failed to create target directory for directory "%s".', $name);

            throw new DirectoryException($msg, 0, $e);
        }

        try {
            $this->tool->mountDirectory($directory->getSource(), $directory->getSource());
        } catch (PharException $e) {
            $msg = sprintf('Failed to mount source directory for directory "%s"', $name);

            throw new DirectoryException($msg, 0, $e);
        }

        $directory->setArchive(Tool::sanitize($name));

        try {
            $cmdBefore = $directory->getCommandBefore();
            if ($cmdBefore) {
                $this->tool->execute($cmdBefore);

                $this->logger->use('app')->info(sprintf('Command was executed: %s', 'BEFORE'));
            }

            $this->tool->createArchive($directory);

            $cmdAfter = $directory->getCommandAfter();
            if ($cmdAfter) {
                $this->tool->execute($cmdAfter);

                $this->logger->use('app')->info(sprintf('Command was executed: %s', 'AFTER'));
            }
        } catch (ToolException $e) {
            $msg = sprintf('Failed to create archive for directory "%s".', $name);

            throw new DirectoryException($msg, 0, $e);
        }

        $this->logger->use('app')->info(sprintf('Archive of directory "%s" created.', $name));
    }
}
