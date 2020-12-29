<?php

/*
 * @package    Backup
 * @author     BloodhunterD <bloodhunterd@bloodhunterd.com>
 * @link       https://github.com/bloodhunterd
 * @copyright  © 2020 BloodhunterD
 */

declare(strict_types=1);

namespace Backup\Manager\Model;

use Backup\Manager\Interfaces\Downloadable;

/**
 * Class Server Model
 *
 * @package Backup\Manager\Model
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class ServerModel implements Downloadable
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $target = DIRECTORY_SEPARATOR;

    /**
     * @var string
     */
    private $host;

    /**
     * @var SSHModel
     */
    private $ssh;

    /**
     * @var bool
     */
    private $disabled = false;

    /**
     * Server Model constructor
     *
     * @param mixed[] $server
     */
    public function __construct(array $server)
    {
        # Required
        $this->setName($server['name']);
        $this->setSource($server['source']);
        $this->setHost($server['host']);
        $this->setSSH(new SSHModel($server['ssh']));

        # Optional
        $this->setTarget($server['target'] ?? $this->target);

        if (isset($server['disabled']) && $server['disabled'] === 'yes') {
            $this->disable();
        }
    }

    /**
     * Set name
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
     * Set source path
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
     * Set target path
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
     * Set host
     *
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * @inheritDoc
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Set SSH Model
     *
     * @param SSHModel $ssh
     */
    public function setSSH(SSHModel $ssh): void
    {
        $this->ssh = $ssh;
    }

    /**
     * @inheritDoc
     */
    public function getSSH(): SSHModel
    {
        return $this->ssh;
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
