<?php
/*
 * @package    Backup
 * @author     BloodhunterD <bloodhunterd@bloodhunterd.com>
 * @link       https://github.com/bloodhunterd/backup
 * @copyright  © 2021 BloodhunterD
 */

declare(strict_types=1);

namespace Backup\Agent\Service;

use Backup\Agent\Model\DatabaseModel;
use Backup\Logger;
use Backup\Tool;
use Vection\Component\DI\Annotations\Inject;
use Vection\Component\DI\Traits\AnnotationInjection;

/**
 * Class DatabaseService
 *
 * @package Backup\Agent\Service
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
abstract class DatabaseService
{
    use AnnotationInjection;

    protected const DEFAULT_HOSTS = ['localhost', '127.0.0.1'];

    public const SYSTEM_MARIADB = 'mariadb';
    public const SYSTEM_MONGODB = 'mongodb';
    public const SYSTEM_MYSQL = 'mysql';
    public const SYSTEM_POSTGRES = 'postgres';
    public const TYPE_DOCKER = 'docker';
    public const TYPE_HOST = 'host';

    /**
     * @var DatabaseModel
     */
    protected DatabaseModel $database;

    /**
     * @var Logger
     * @Inject("Backup\Logger")
     */
    protected Logger $logger;

    /**
     * @var Tool
     * @Inject("Backup\Tool")
     */
    protected Tool $tool;

    /**
     * Prepare Docker command
     *
     * @param string $command
     * @return string
     */
    protected function prepareDockerCommand(string $command): string
    {
        $cmd = 'docker exec %s sh -c "%s"';

        return sprintf($cmd, $this->database->getDockerContainer(), $command);
    }

    /**
     * Prepare host
     *
     * @return string
     */
    protected function prepareHost(): string
    {
        $host = $this->database->getHost();

        if (!$host) {
            $host = self::DEFAULT_HOSTS[0];
        } else if (!in_array($host, self::DEFAULT_HOSTS)) {
            $host = escapeshellarg($host);
        }

        return sprintf(' -h %s', $host);
    }

    /**
     * Prepare user
     *
     * @return string
     */
    protected function prepareUser(): string
    {
        // Use constants of child class
        $class = static::class;

        $user = $this->database->getUser();

        if (!$user) {
            $user = $class::DEFAULT_USER;
        } else if (
            $this->database->getType() === $class::TYPE_DOCKER &&
            strncasecmp($user, $class::USER, strlen($class::USER)) === 0
        ) {
            $user = $class::ENV . $class::USER;
        } else {
            $user = escapeshellarg($user);
        }

        return $user;
    }
}
