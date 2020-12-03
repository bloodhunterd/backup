<?php
/*
 * This file is part of the Backup project.
 * Visit project at https://github.com/bloodhunterd/backup
 *
 * Copyright © 2020 BloodhunterD <bloodhunterd@bloodhunterd.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

require_once __DIR__ . '/../config/path.php';

// Include only necessary folders and files
$regex = '/\bconfig\b|\bres\b|\bsrc\b|\bvendor\b|\bcomposer\.(json|lock)\b|\bindex\.php\b/';

$phar = new Phar(__DIR__ . '/backup.phar');
$phar->buildFromDirectory(ROOT_DIR, $regex);
$phar->setDefaultStub('index.php');
