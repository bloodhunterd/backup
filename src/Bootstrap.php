<?php
/*
 * This file ist part of the Backup project, see https://github.com/bloodhunterd/Backup.
 * © 2021 BloodhunterD <bloodhunterd@bloodhunterd.com>
 */

declare(strict_types=1);

namespace Backup;

use Backup\Agent\Agent;
use Backup\Exception\BackupException;
use Backup\Exception\ConfigurationException;
use Backup\Interfaces\Backup;
use Backup\Manager\Manager;
use Backup\Report\Report;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Vection\Component\DI\Container;
use Vection\Contracts\Validator\Schema\PropertyExceptionInterface;
use Vection\Contracts\Validator\Schema\SchemaExceptionInterface;

/**
 * Class Bootstrap
 *
 * @package Backup
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class Bootstrap
{

    /**
     * @var Container
     */
    private Container $container;

    /**
     * @var string
     */
    private string $configPath;

    /**
     * Bootstrap constructor.
     * @param string $configPath
     */
    public function __construct(string $configPath)
    {
        # Initialize dependency injection
        $this->container = new Container();
        $this->container->registerNamespace([
            'Backup',
            'Monolog'
        ]);

        $this->configPath = $configPath;
    }

    /**
     * Initialize the backup application
     *
     * @return Backup
     * @throws BackupException|PropertyExceptionInterface|SchemaExceptionInterface
     */
    public function init(): Backup
    {
        /** @var Logger $logger */
        $logger = new Logger(LOGGER_APP);
        $logger->pushHandler(
            (new StreamHandler('php://stdout'))->setFormatter(
                new LineFormatter(null, 'Y-m-d H:i:s')
            )
        );

        // Add logger for injection
        $this->container->add($logger);

        /** @var Tool $tool */
        $tool = $this->container->get(Tool::class);

        /** @var Configuration $config */
        $config = $this->container->get(Configuration::class);
        $config->setPath($this->configPath);

        try {
            $config->load();
        } catch (ConfigurationException $e) {
            $logger->error($e->getMessage());

            throw new BackupException($e->getMessage(), 0, $e);
        }

        $tool->setTimezone($config->getTimezone());

                /** @var StreamHandler[] $handlers */
        $handlers = $logger->getHandlers();
        # Set log level from configuration
        foreach ($handlers as $handler) {
            $handler->setLevel($config->isDebugEnabled() ? Logger::DEBUG : Logger::INFO);
        }
        $logger->setHandlers($handlers);

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

                $logger->info(sprintf('Mode set to "%s".', $mode));
                break;
            case 'manager':
                /** @var Manager $backup */
                $backup = $this->container->get(Manager::class);

                $logger->info(sprintf('Mode set to "%s".', $mode));
                break;
            default:
                $msg = sprintf('The mode "%s" is not supported. Valid modes are "agent" or "manager".', $mode);

                $logger->error($msg);

                throw new BackupException($msg);
        }

        $logger->info('Backup initialized.');

        return $backup;
    }
}
