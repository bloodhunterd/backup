<?php

/**
 * This file is part of the Backup project.
 * Visit project at https://github.com/bloodhunterd/backup
 *
 * © BloodhunterD <backup@bloodhunterd.com> | 2019
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Backup;

use Backup\Exceptions\Configuration as ConfigurationException;
use Locale;
use Phar;
use PharException;
use Vection\Component\DI\Container;

/**
 * Class Bootstrap
 *
 * @author BloodhunterD
 *
 * @package BackupAgent
 */
class Bootstrap
{

    /**
     * @var Container
     */
    public $container;

    /**
     * Bootstrap constructor
     */
    public function __construct()
    {
        $this->container = new Container();
        $this->container->registerNamespace([
            'Backup',
        ]);
    }

    /**
     * Initialize the agent
     *
     * @return Agent
     * @throws ConfigurationException
     */
    public function init(): object
    {
        $config = new Configuration();
        $config->mount();
        $config->load();

        $this->container->add($config);

        $this->setTimezone($config->getTimezone());
        $this->setLanguage($config->getLanguage());

        return $this->container->get(Agent::class);
    }

    /**
     * Set the timezone
     *
     * @param string $timezone
     * @throws ConfigurationException
     */
    private function setTimezone(string $timezone): void
    {
        if (!date_default_timezone_set($timezone)) {
            $msg = 'The timezone "%s" is invalid.';

            throw new ConfigurationException(sprintf($msg, $timezone));
        }
    }

    /**
     * Set the language
     *
     * @param string $language
     * @throws ConfigurationException
     */
    private function setLanguage(string $language): void
    {
        if (!Locale::setDefault($language)) {
            $msg = 'The language "%s" is not supported or not installed.';

            throw new ConfigurationException(sprintf($msg, $language));
        }
    }

    /**
     * Mount backup target directory
     *
     * @throws ConfigurationException
     */
    private function mountTargetDirectory(): void
    {
        /** @var Configuration $config */
        $config = $this->container->get(Configuration::class);

        try {
            Phar::mount($config->getTargetDirectory(), $config->getTargetDirectory());
        } catch (PharException $e) {
            $msg = 'Failed to mount the target directory "%s". Please check %s.';

            throw new ConfigurationException(sprintf($msg, $config->getTargetDirectory(), $e->getMessage()));
        }
    }
}
