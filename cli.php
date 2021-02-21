<?php
/*
 * This file ist part of the Backup project, see https://github.com/bloodhunterd/Backup.
 * © 2021 BloodhunterD <bloodhunterd@bloodhunterd.com>
 */

declare(strict_types=1);

use Backup\Bootstrap;

require_once __DIR__ . '/config/constants.php';
require_once VENDOR_DIR . DIRECTORY_SEPARATOR . 'autoload.php';

$shortOptions = [
    'c:'
];

$longOptions = [
    '--config:'
];

try {
    $options = getopt(implode($shortOptions), $longOptions);
    if (!$options) {
        throw new RuntimeException('Failed to parse given arguments. Maybe they are invalid or missing?');
    }

    if (isset($options['config']) && is_string($options['config'])) {
        $configPath = $options['config'];
    } else if (isset($options['c']) && is_string($options['c'])) {
        $configPath = $options['c'];
    } else {
        throw new RuntimeException('Parameter "-c" or "--config" for the configuration file path is missing.');
    }

    (new Bootstrap($configPath))->init()->run();
} catch (Exception $e) {
    $previous = '';
    if ($e->getPrevious()) {
        $previous = <<<previous
        
        \033[1;31m#\033[0m Previous
        \033[1;31m# \033[0m \033[1;33m{$e->getPrevious()->getCode()}\033[0m | \033[1;32m{$e->getPrevious()->getMessage()}
        previous;
    }

    print <<<error
    \033[1;31m# ERROR\033[0m
    \033[1;31m# \033[1;33m{$e->getCode()}\033[0m | \033[1;32m{$e->getMessage()}{$previous}
    \033[1;31m#\033[0m \033[1;34mTrace
    \033[1;31m#\033[0m {$e->getTraceAsString()}
    error;

    exit();
}
