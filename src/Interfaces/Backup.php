<?php
/*
 * This file ist part of the Backup project, see https://github.com/bloodhunterd/Backup.
 * © 2021 BloodhunterD <bloodhunterd@bloodhunterd.com>
 */

declare(strict_types=1);

namespace Backup\Interfaces;

use Backup\Exception\BackupException;

/**
 * Interface Backup
 *
 * @package Backup\Interfaces
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
interface Backup
{
    public const TYPE_DIRECTORY = 'DIRECTORY';
    public const TYPE_DATABASE = 'DATABASE';
    public const TYPE_SERVER = 'SERVER';

    /**
     * Run the backup
     *
     * @throws BackupException
     */
    public function run(): void;
}
