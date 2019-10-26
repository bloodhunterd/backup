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

declare(strict_types = 1);

namespace Backup;

use Backup\Exception\ConfigurationException;
use Backup\Interfaces\Backup;
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
     * @throws ConfigurationException | Exception
     */
    public function init(): Backup
    {
        /** @var Logger $logger */
        $logger = $this->container->get(Logger::class);

        # Initialize application logging
        $logger->set(
            (new MonologLogger('app'))
                ->pushHandler(
                    (new StreamHandler('php://stdout'))
                        ->setFormatter($logger->getLineFormatter())
            )
        );

        $logger->use('app')->info('Backup preparing');

        /** @var Tool $tool */
        $tool = $this->container->get(Tool::class);

        $tool->mountDirectory(LOG_DIR);

        # Update application logging
        $logger->use('app')
            ->pushHandler(
                (new StreamHandler(LOG_DIR . DIRECTORY_SEPARATOR . 'backup.app.log'))
                    ->setFormatter($logger->getLineFormatter())
            );

        $logger->use('app')->info('Backup initializing');

        # Initialize RSYNC logging
        $logger->set(
            (new MonologLogger('shell'))
                ->pushHandler(
                    (new StreamHandler('php://stdout'))
                        ->setFormatter($logger->getLineFormatter())
                )
                ->pushHandler(
                    (new StreamHandler(LOG_DIR . DIRECTORY_SEPARATOR . 'backup.shell.log'))
                        ->setFormatter($logger->getLineFormatter())
                )
        );

        # Initialize report logging
        $logger->set((new MonologLogger('report'))
            ->pushHandler(
                (new StreamHandler(LOG_DIR . DIRECTORY_SEPARATOR . 'backup.report.log', MonologLogger::INFO))
                    ->setFormatter($logger->getLineFormatter())
            )
        );

        /** @var Configuration $config */
        $config = $this->container->get(Configuration::class);
        $config->mount();
        $config->load();

        $tool->setTimezone($config->getTimezone());
        $tool->setLanguage($config->getLanguage());

        switch ($config->getMode()) {
            case 'agent':
                /** @var Agent $backup */
                $backup = $this->container->get(Agent::class);

                $logger->use('app')->info('Starting Agent');
                break;
            case 'manager':
                /** @var Manager $backup */
                $backup = $this->container->get(Manager::class);

                $logger->use('app')->info('Starting Manager');
                break;
            default:
                throw new ConfigurationException(sprintf('The mode "%s" is invalid.', $config->getMode()));
        }

        $tool->mountDirectory($config->getTargetDirectory());

        $logger->use('app')->info('Backup initialized');

        return $backup;
    }
}
