<?php

/**
 * This file is part of the Backup project.
 * Visit project at https://github.com/bloodhunterd/backup
 *
 * Copyright © 2020 BloodhunterD <bloodhunterd@bloodhunterd.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Backup\Agent\Service;

use Backup\Agent\Service\Database\MySqlService;
use Backup\Exception\DatabaseException;
use Backup\Agent\Model\DatabaseModel;
use Vection\Component\DI\Annotations\Inject;

/**
 * Class DatabaseService
 *
 * @package Backup\Agent\Service
 *
 * @author BloodhunterD
 */
class DatabaseService
{
    public const SYSTEM_MARIADB = 'mariadb';
    public const SYSTEM_MONGODB = 'mongodb';
    public const SYSTEM_MYSQL = 'mysql';
    public const SYSTEM_POSTGRESQL = 'postgresql';
    public const TYPE_DOCKER = 'docker';
    public const TYPE_HOST = 'host';

    /**
     * @var MySqlService
     * @Inject("Backup\Agent\Service\Database\MySqlService")
     */
    private $mySqlService;

    /**
     * Backup a database
     *
     * @param DatabaseModel $database
     *
     * @throws DatabaseException
     */
    public function backupDatabase(DatabaseModel $database): void
    {
        switch ($database->getSystem()) {
            case self::SYSTEM_POSTGRESQL:
                // Todo: Implement PostgreSQL database service
                throw new DatabaseException('PostgreSQL support not available, yet.');
            case self::SYSTEM_MONGODB:
                // Todo: Implement MongoDB database service
                throw new DatabaseException('MongoDB support not available, yet.');
            case self::SYSTEM_MARIADB:
            case self::SYSTEM_MYSQL:
            default:
                $this->mySqlService->backupDatabase($database);
        }
    }
}
