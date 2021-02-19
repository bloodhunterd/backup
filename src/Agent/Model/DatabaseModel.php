<?php
/*
 * This file ist part of the Backup project, see https://github.com/bloodhunterd/Backup.
 * © 2021 BloodhunterD <bloodhunterd@bloodhunterd.com>
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
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class DatabaseModel implements Compressible
{
    use AnnotationInjection;

    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $archive;

    /**
     * @var string
     */
    private string $system;

    /**
     * @var string
     */
    private string $type;

    /**
     * @var string
     */
    private string $dockerContainer;

    /**
     * @var string
     */
    private string $host;

    /**
     * @var string
     */
    private string $user;

    /**
     * @var string
     */
    private string $password;

    /**
     * @var string
     */
    private string $source;

    /**
     * @var string
     */
    private string $target;

    /**
     * @var bool
     */
    private bool $disabled = false;

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
        $this->setHost($source['host'] ?? 'localhost');
        $this->setPassword($source['password'] ?? '');
        $this->setSystem($source['system'] ?? DatabaseService::SYSTEM_MYSQL);
        $this->setTarget($database['target'] ?? DIRECTORY_SEPARATOR);
        $this->setType($source['type'] ?? DatabaseService::TYPE_HOST);
        $this->setUser($source['user'] ?? '');

        if (isset($database['disabled']) && $database['disabled']) {
            $this->disable();
        }

        # Special handling for host or docker databases
        if ($this->type === DatabaseService::TYPE_DOCKER) {
            # Required
            $this->setDockerContainer($source['container']);
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
     * Set the archive file name
     *
     * @param string $name
     */
    public function setArchive(string $name): void
    {
        $this->archive = $name;
    }

    /**
     * Get the archive file name
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
