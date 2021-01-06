<?php
/*
 * @package    Backup
 * @author     BloodhunterD <bloodhunterd@bloodhunterd.com>
 * @link       https://github.com/bloodhunterd/backup
 * @copyright  © 2021 BloodhunterD
 */

declare(strict_types=1);

namespace Backup\Agent\Service;

use Backup\Agent\Service\Database\MongoDbService;
use Backup\Agent\Service\Database\MySqlService;
use Backup\Exception\DatabaseException;
use Backup\Agent\Model\DatabaseModel;
use Vection\Component\DI\Annotations\Inject;
use Vection\Component\DI\Traits\AnnotationInjection;

/**
 * Class DatabaseService
 *
 * @package Backup\Agent\Service
 *
 * @author BloodhunterD
 */
class DatabaseService
{
    use AnnotationInjection;

    public const SYSTEM_MARIADB = 'mariadb';
    public const SYSTEM_MONGODB = 'mongodb';
    public const SYSTEM_MYSQL = 'mysql';
    public const SYSTEM_POSTGRESQL = 'postgresql';
    public const TYPE_DOCKER = 'docker';
    public const TYPE_HOST = 'host';

    /**
     * @var MongoDbService
     * @Inject("Backup\Agent\Service\Database\MongoDbService")
     */
    private $mongoDbService;

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
                $this->mongoDbService->backupDatabase($database);
                break;
            case self::SYSTEM_MARIADB:
            case self::SYSTEM_MYSQL:
            default:
                $this->mySqlService->backupDatabase($database);
        }
    }
}
