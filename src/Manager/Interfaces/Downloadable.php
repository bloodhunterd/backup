<?php
/*
 * This file ist part of the Backup project, see https://github.com/bloodhunterd/Backup.
 * © 2021 BloodhunterD <bloodhunterd@bloodhunterd.com>
 */

declare(strict_types=1);

namespace Backup\Manager\Interfaces;

use Backup\Manager\Model\SSHModel;

/**
 * Interface Downloadable
 *
 * @package Backup\Manager\Interfaces
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
interface Downloadable
{

    /**
     * Get the name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the source directory
     *
     * @return string
     */
    public function getSource(): string;

    /**
     * Get the target directory
     *
     * @return string
     */
    public function getTarget(): string;

    /**
     * Get the host address
     *
     * @return string
     */
    public function getHost(): string;

    /**
     * Get the SSH settings
     *
     * @return SSHModel
     */
    public function getSSH(): SSHModel;
}
