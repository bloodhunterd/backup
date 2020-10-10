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

namespace Backup;

use Backup\Agent\Agent;
use Backup\Exception\BackupException;
use Backup\Exception\ConfigurationException;
use Backup\Interfaces\Backup;
use Backup\Manager\Manager;
use Backup\Report\Report;
use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;
use Vection\Component\DI\Container;

/**
 * Class Bootstrap
 *
 * @package Backup
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class Bootstrap
{

    /**
     * @var Container
     */
    private $container;

    /**
     * Bootstrap constructor
     */
    public function __construct()
    {
        # Initialize dependency injection
        $this->container = new Container();
        $this->container->registerNamespace([
            'Backup'
        ]);
    }

    /**
     * Initialize the backup application
     *
     * @return Backup
     * @throws Exception
     */
    public function init(): Backup
    {
        /** @var Logger $logger */
        $logger = $this->container->get(Logger::class);

        # Logging channels
        $channels = ['app', 'console'];

        # Initialize logger channels
        foreach ($channels as $channel) {
            $logger->set(
                (new MonologLogger($channel))
                    ->pushHandler(
                        (new StreamHandler('php://stdout'))
                            ->setFormatter($logger->getLineFormatter())
                    )
            );
        }

        /** @var Tool $tool */
        $tool = $this->container->get(Tool::class);

        /** @var Configuration $config */
        $config = $this->container->get(Configuration::class);

        try {
            $config->mount();
            $config->load();
        } catch (ConfigurationException $e) {
            $logger->use('app')->error($e->getMessage());

            throw new BackupException($e->getMessage(), 0, $e);
        }

        $tool->setTimezone($config->getTimezone());

        # Set log level from configuration
        foreach ($channels as $channel) {
            /** @var StreamHandler[] $handlers */
            $handlers = $logger->use($channel)->getHandlers();

            foreach ($handlers as $handler) {
                $handler->setLevel($config->isDebugEnabled() ? MonologLogger::DEBUG : MonologLogger::INFO);
            }

            $logger->use('app')->setHandlers($handlers);
        }

        /** @var Report $report */
        $report = $this->container->get(Report::class);
        $report->setSender($config->getReportSender());
        $report->setSubject($config->getReportSubject());

        foreach ($config->getReportRecipients() as $recipient) {
            $report->addRecipient($recipient);
        }

        $mode = $config->getMode();
        switch ($mode) {
            case 'agent':
                /** @var Agent $backup */
                $backup = $this->container->get(Agent::class);

                $logger->use('app')->info(sprintf('Mode set to "%s".', $mode));
                break;
            case 'manager':
                /** @var Manager $backup */
                $backup = $this->container->get(Manager::class);

                $logger->use('app')->info(sprintf('Mode set to "%s".', $mode));
                break;
            default:
                $msg = sprintf('The mode "%s" is not supported. Valid modes are "agent" or "manager".', $mode);

                $logger->use('app')->error($msg);

                throw new BackupException($msg);
        }

        $tool->mountDirectory('/backup', $config->getTargetDirectory());

        $logger->use('app')->info('Backup initialized.');

        return $backup;
    }
}
