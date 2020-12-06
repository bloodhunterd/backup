<?php

/**
 * This file is part of the Backup project.
 * Visit project at https://github.com/bloodhunterd/backup
 *
 * Copyright © 2020 BloodhunterD <bloodhunterd@bloodhunterd.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Backup\Agent\Model;

use Backup\Interfaces\Compressible;
use Backup\Agent\Service\DatabaseService;
use Vection\Component\DI\Traits\AnnotationInjection;

/**
 * Class DatabaseModel
 *
 * @package Backup\Agent\Model
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class DatabaseModel implements Compressible
{
    use AnnotationInjection;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $archive;

    /**
     * @var string
     */
    private $system;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $dockerContainer;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $target;

    /**
     * @var bool
     */
    private $disabled = false;

    /**
     * DatabaseModel constructor
     *
     * @param mixed[] $database
     *
     */
    public function __construct(array $database)
    {
        $source = $database['source'];

        # Required
        $this->setName($database['name']);
        # Source has to be empty
        $this->setSource('');

        # Optional
        $this->setTarget($database['target'] ?? DIRECTORY_SEPARATOR);
        $this->setSystem($source['system'] ?? DatabaseService::SYSTEM_MYSQL);
        $this->setType($source['type'] ?? DatabaseService::TYPE_HOST);
        $this->setUser($source['user'] ?? 'root');
        $this->setPassword($source['password'] ?? '');

        if (isset($database['disabled']) && $database['disabled'] === 'yes') {
            $this->disable();
        }

        # Special handling for host or docker databases
        if ($this->type === DatabaseService::TYPE_DOCKER) {
            # Required
            $this->setDockerContainer($source['container']);
        } else {
            # Optional
            $this->setHost($source['host'] ?? 'localhost');
        }
    }

    /**
     * Set the name
     *
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the source
     *
     * @param string $path
     */
    public function setSource(string $path): void
    {
        $this->source = $path;
    }

    /**
     * @inheritDoc
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * Set the target
     *
     * @param string $path
     */
    public function setTarget(string $path): void
    {
        $this->target = $path;
    }

    /**
     * @inheritDoc
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * Set the archive
     *
     * @param string $name
     */
    public function setArchive(string $name): void
    {
        $this->archive = $name . '.sql.bz2';
    }

    /**
     * Get the archive
     *
     * @return string
     */
    public function getArchive(): string
    {
        return $this->archive;
    }

    /**
     * Set the system
     *
     * @param string $system
     */
    public function setSystem(string $system): void
    {
        $this->system = $system;
    }

    /**
     * Get the system
     *
     * @return string
     */
    public function getSystem(): string
    {
        return $this->system;
    }

    /**
     * Set the type
     *
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * Get the type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set the docker container
     *
     * @param string $container
     */
    public function setDockerContainer(string $container): void
    {
        $this->dockerContainer = $container;
    }

    /**
     * Get the docker container
     *
     * @return string
     */
    public function getDockerContainer(): string
    {
        return $this->dockerContainer;
    }

    /**
     * Set the host
     *
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * Get the host
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Set the user
     *
     * @param string $user
     */
    public function setUser(string $user): void
    {
        $this->user = $user;
    }

    /**
     * Get the user
     *
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * Set the password
     *
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * Get the password
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Disable
     */
    public function disable(): void
    {
        $this->disabled = true;
    }

    /**
     * Is disabled
     *
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->disabled;
    }
}
