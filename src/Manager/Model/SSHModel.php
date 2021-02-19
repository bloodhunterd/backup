<?php
/*
 * This file ist part of the Backup project, see https://github.com/bloodhunterd/Backup.
 * © 2021 BloodhunterD <bloodhunterd@bloodhunterd.com>
 */

declare(strict_types=1);

namespace Backup\Manager\Model;

/**
 * Class SSHModel
 *
 * @package Backup\Manager\Model
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class SSHModel
{

    /**
     * @var int
     */
    private int $port = 22;

    /**
     * @var string
     */
    private string $user = 'root';

    /**
     * @var string
     */
    private string $key;

    /**
     * @var string
     */
    private string $passphrase = '';

    /**
     * SSH Model constructor
     *
     * @param mixed[] $settings
     */
    public function __construct(array $settings)
    {
        # Required
        $this->setKey($settings['key']);

        # Optional
        $this->setPort($settings['port'] ?? $this->port);
        $this->setUser($settings['user'] ?? $this->user);
        $this->setPassphrase($settings['passphrase'] ?? $this->passphrase);
    }

    /**
     * Set port
     *
     * @param int $port
     */
    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    /**
     * Get port
     *
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * Set user
     *
     * @param string $user
     */
    public function setUser(string $user): void
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * Set key path
     *
     * @param string $path
     */
    public function setKey(string $path): void
    {
        $this->key = $path;
    }

    /**
     * Get key path
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Set key passphrase
     *
     * @param string $passphrase
     */
    public function setPassphrase(string $passphrase): void
    {
        $this->passphrase = $passphrase;
    }

    /**
     * Get key passphrase
     *
     * @return string
     */
    public function getPassphrase(): string
    {
        return $this->passphrase;
    }
}
