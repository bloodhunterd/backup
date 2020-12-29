<?php

/*
 * @package    Backup
 * @author     BloodhunterD <bloodhunterd@bloodhunterd.com>
 * @link       https://github.com/bloodhunterd
 * @copyright  © 2020 BloodhunterD
 */

declare(strict_types=1);

use Backup\Bootstrap;

require_once __DIR__ . '/config/path.php';
require_once VENDOR_DIR . DIRECTORY_SEPARATOR . 'autoload.php';

try {
    (new Bootstrap())->init()->run();
} catch (Exception $e) {
    echo '- - - ERROR - - -' . "\n";
    echo 'Message: ' . $e->getMessage() . "\n";
    echo 'Code:' . $e->getCode() . "\n";

    if ($e->getPrevious()) {
        echo 'Previous message: ' . $e->getPrevious()->getMessage() . "\n";
        echo 'Previous code: ' . $e->getPrevious()->getCode() . "\n";
    }

    echo 'Trace: ' . "\n";
    echo $e->getTraceAsString() . "\n";

    exit('- - - END - - -');
}
