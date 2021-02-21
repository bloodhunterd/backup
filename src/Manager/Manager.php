<?php
/*
 * This file ist part of the Backup project, see https://github.com/bloodhunterd/Backup.
 * © 2021 BloodhunterD <bloodhunterd@bloodhunterd.com>
 */

declare(strict_types=1);

namespace Backup\Manager;

use Backup\Configuration;
use Backup\Exception\DownloadException;
use Backup\Exception\DirectoryException;
use Backup\Exception\ToolException;
use Backup\Interfaces\Backup;
use Monolog\Logger;
use Backup\Manager\Model\ServerModel;
use Backup\Manager\Service\DownloadService;
use Backup\Report\Report;
use Backup\Tool;
use Vection\Component\DI\Annotations\Inject;
use Vection\Component\DI\Traits\AnnotationInjection;

/**
 * Class Manager
 *
 * @package Backup\Manager
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class Manager implements Backup
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
     * @inheritDoc
     */
    public function run(): void
    {
        $servers = $this->config->getServers();

        if (!$servers) {
            $this->logger->warning('No servers set in configuration.');
        }

        foreach ($servers as $server) {
            $serverModel = new ServerModel($server);

            if ($serverModel->isDisabled()) {
                $this->logger->info(
                    sprintf('Backup of server "%s" is disabled.', $serverModel->getName())
                );

                $this->report->add(
                    Report::RESULT_INFO,
                    self::TYPE_SERVER,
                    'Backup disabled.',
                    $serverModel
                );

                continue;
            }

            try {
                $this->tool->setDurationStart();

                $this->backupServer($serverModel);

                $duration = $this->tool->getDuration();
            } catch (DownloadException | DirectoryException $e) {
                $this->logger->error($e->getMessage());
                $this->logger->debug($e->getTraceAsString());

                $this->report->add(
                    Report::RESULT_ERROR,
                    self::TYPE_SERVER,
                    $e->getMessage(),
                    $serverModel
                );

                continue;
            }

            $cmd = sprintf('du -sb %s', $serverModel->getTarget());

            try {
                $fileSize = (int) $this->tool->execute($cmd)[0];
            } catch (ToolException $e) {
                $this->logger->error($e->getMessage());
                $this->logger->debug($e->getTraceAsString());

                $fileSize = null;
            }

            $this->report->add(
                Report::RESULT_OK,
                self::TYPE_SERVER,
                'Files downloaded.',
                $serverModel,
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
     * @param ServerModel $server
     *
     * @throws DownloadException | DirectoryException
     */
    public function backupServer(ServerModel $server): void
    {
        $name = $server->getName();

        if (!$this->tool->createDirectory($server->getTarget())) {
            $msg = sprintf('Failed to create target directory for directory "%s".', $name);

            throw new DirectoryException($msg);
        }

        $this->logger->info(sprintf('Target directory "%s" created.', $name));

        $server->setTarget($this->config->getTargetDirectory() . $server->getTarget());

        try {
            $this->tool->execute((new DownloadService())->getCmd($server));
        } catch (ToolException $e) {
            $msg = sprintf('Failed to download from server "%s".', $name);

            throw new DownloadException($msg, 0, $e);
        }

        $this->logger->info(sprintf('Archive "%s" downloaded from server.', $name));
    }
}
