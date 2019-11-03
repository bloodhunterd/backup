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

namespace Backup\Manager\Service;

use Backup\Manager\Interfaces\Downloadable;

/**
 * Class DownloadService
 *
 * @package Backup\Manager\Service
 *
 * @author BloodhunterD
 */
class DownloadService
{

    /**
     * Create download command
     *
     * @param Downloadable $download
     * @return string
     */
    public function getCmd(Downloadable $download): string
    {
        # RSYNC:
        # -r Recursive
        # -t Preserves modification times.
        # -v Increases verbosity. (debug mode only)
        # -e Uses an alternative remote shell program for communication between the local and remote copies. (SSH)
        # SSH:
        # -q Quiet mode. Causes most warning and diagnostic messages to be suppressed.
        # -p Port to connect to on the remote host.
        # -i Identity file. Selects a file from which the identity (private key) for authentication is read.
        return sprintf(
            'rsync -r -t -e "ssh -t -q -o "StrictHostKeyChecking=no" -p %d -i %s" %s@%s:%s %s',
            $download->getSSH()->getPort(),
            escapeshellarg($download->getSSH()->getKey()),
            $download->getSSH()->getUser(),
            $download->getHost(),
            escapeshellarg($download->getSource() . DIRECTORY_SEPARATOR),
            escapeshellarg($download->getTarget() . DIRECTORY_SEPARATOR)
        );
    }
}
