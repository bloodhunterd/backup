<?php

/*
 * @package    Backup
 * @author     BloodhunterD <bloodhunterd@bloodhunterd.com>
 * @link       https://github.com/bloodhunterd
 * @copyright  © 2020 BloodhunterD
 */

declare(strict_types=1);

namespace Backup\Tests;

use Backup\Manager\Interfaces\Downloadable;
use Backup\Manager\Model\ServerModel;
use Backup\Manager\Service\DownloadService;
use PHPUnit\Framework\TestCase;

/**
 * Class DownloadTest
 *
 * @package Backup\Tests
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class DownloadTest extends TestCase
{

    /**
     * Test download command
     *
     * @dataProvider dataDownloadCmd
     *
     * @param Downloadable $download
     * @param string $cmd
     */
    public function testDownloadCmd(Downloadable $download, string $cmd): void
    {
        self::assertEquals($cmd, (new DownloadService())->getCmd($download));
    }

    /**
     * Data download command
     *
     * @return array[]
     */
    public function dataDownloadCmd(): array
    {
        $download = new ServerModel([
            'name' => '',
            'host' => '127.0.0.1',
            'source' => '/backup',
            'target' => '/backup/My/Target/Folder No-1',
            'ssh' => [
                'port' => 2222,
                'user' => 'backupuser',
                'key' => '/root/.ssh/id_rsa'
            ]
        ]);

        $cmd = sprintf(
            'rsync -r -t -e "ssh -t -q -o "StrictHostKeyChecking=no" -p %d -i %s" %s@%s:%s %s',
            2222,
            escapeshellarg('/root/.ssh/id_rsa'),
            'backupuser',
            '127.0.0.1',
            escapeshellarg('/backup' . DIRECTORY_SEPARATOR),
            escapeshellarg('/backup/My/Target/Folder No-1' . DIRECTORY_SEPARATOR)
        );

        return [
            [$download, $cmd]
        ];
    }
}
