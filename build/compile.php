<?php
/*
 * @package    Backup
 * @author     BloodhunterD <bloodhunterd@bloodhunterd.com>
 * @link       https://github.com/bloodhunterd
 * @copyright  © 2020 BloodhunterD
 */

declare(strict_types = 1);

require_once __DIR__ . '/../config/path.php';

// Include only necessary folders and files
$regex = '/\bconfig\b|\bres\b|\bsrc\b|\bvendor\b|\bcomposer\.(json|lock)\b|\bindex\.php\b/';

$phar = new Phar(__DIR__ . '/backup.phar');
$phar->buildFromDirectory(ROOT_DIR, $regex);
$phar->setDefaultStub('index.php');
