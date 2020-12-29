<?php

/*
 * @package    Backup
 * @author     BloodhunterD <bloodhunterd@bloodhunterd.com>
 * @link       https://github.com/bloodhunterd
 * @copyright  © 2020 BloodhunterD
 */

declare(strict_types=1);

namespace Backup\Interfaces;

/**
 * Interface Compressible
 *
 * @package Backup\Interfaces
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
interface Compressible
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
     * Get the archive name
     *
     * @return string
     */
    public function getArchive(): string;
}
