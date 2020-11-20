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

declare(strict_types=1);

namespace Backup;

use Backup\Exception\ToolException;
use Backup\Interfaces\Compressible;
use Phar;
use Vection\Component\DI\Annotations\Inject;
use Vection\Component\DI\Traits\AnnotationInjection;

/**
 * Class Tool
 *
 * @package Backup
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class Tool
{
    use AnnotationInjection;

    public const TIME_NANOSECONDS = 8;
    public const TIME_MICROSECONDS = 16;
    public const TIME_MILLISECONDS = 32;
    public const TIME_SECONDS = 64;
    public const TIME_MINUTES = 128;
    public const TIME_HOURS = 256;

    /**
     * @var int|float
     */
    private $durationStart = 0;

    /**
     * @var Configuration
     * @Inject("Backup\Configuration")
     */
    private $config;

    /**
     * @var Logger
     * @Inject("Backup\Logger")
     */
    private $logger;

    /**
     * Set the timezone
     *
     * @param string $timezone
     */
    public function setTimezone(string $timezone): void
    {
        if (date_default_timezone_set($timezone)) {
            $this->logger->use('app')->info(sprintf('Timezone set to "%s".', $timezone));

            return;
        }

        $this->logger->use('app')->warning(sprintf(
            'The timezone "%s" is either not supported or installed. Use fallback timezone "%s" instead.',
            $timezone,
            date_default_timezone_get()
        ));
    }

    /**
     * Set a locale for a category
     *
     * @param int $category
     * @param string $locale
     * @return bool
     */
    private function setLocale(int $category, string $locale): bool
    {
        return setlocale($category, $locale) === $locale;
    }

    /**
     * Set the language
     *
     * @param string $locale
     */
    public function setLanguage(string $locale): void
    {
        if ($this->setLocale(LC_ALL, $locale)) {
            $this->logger->use('app')->info(sprintf('Language set to "%s".', $locale));

            return;
        }

        $this->logger->use('app')->warning(sprintf(
            'The language "%s" is either not supported or installed. Fallback to "%s".',
            $locale,
            $this->setLocale(LC_ALL, '0')
        ));
    }

    /**
     * Mount a directory
     *
     * @param string $internalPath
     * @param string $externalPath
     */
    public function mountDirectory(string $internalPath, string $externalPath): void
    {
        Phar::mount($internalPath, $externalPath);

        $this->logger->use('app')->info(sprintf('Directory "%s" is mounted as "%s".', $externalPath, $internalPath));
    }

    /**
     * Create a directory
     *
     * @param string $path
     *
     * @throws ToolException
     */
    public function createDirectory(string $path): void
    {
        $absolutePath = $this->config->getTargetDirectory() . $path;

        if (!is_dir($absolutePath)) {
            $cmd = sprintf('mkdir -p %s', escapeshellarg($absolutePath));

            $this->execute($cmd);
        }

        $this->logger->use('app')->info(sprintf('Directory "%s" created.', $absolutePath));
    }

    /**
     * Create an archive
     *
     * @param Compressible $object
     *
     * @throws ToolException
     */
    public function createArchive(Compressible $object): void
    {
        $target = $object->getTarget() . DIRECTORY_SEPARATOR . $object->getArchive();

        $cmd = sprintf(
            'tar -cjf %s %s',
            escapeshellarg($this->config->getTargetDirectory() . $target),
            escapeshellarg($object->getSource())
        );

        try {
            $this->execute($cmd);
        } catch (ToolException $e) {
            switch ($e->getCode()) {
                case 1:
                    $msg = ' Some files changed while archiving.';
                    break;
                case 2:
                    $msg = ' A fatal, unrecoverable error occurred.';
                    break;
            }

            $this->logger->use('app')->error(
                sprintf('Failed to create archive for "%s.%s".', $object->getName(), $msg)
            );

            return;
        }

        $this->logger->use('app')->info(sprintf('Archive "%s" created.', $target));
    }

    /**
     * Execute a command
     *
     * @param string $command
     *
     * @return string[]
     * @throws ToolException
     */
    public function execute(string $command): array
    {
        $this->logger->use('app')->debug(sprintf('Execute command: %s', $command));

        exec($command, $output, $return);

        foreach ($output as $line) {
            $this->logger->use('console')->debug($line);
        }

        $this->logger->use('app')->debug(sprintf('Return status: %d', $return));

        # If the return status is not zero, the command failed
        if ($return !== 0) {
            throw new ToolException(sprintf('Failed to execute command: %s', $command), $return);
        }

        return $output;
    }

    /**
     * Set start time for duration calculation
     */
    public function setDurationStart(): void {
        $this->durationStart = hrtime(true);
    }

    /**
     * Get duration in nanoseconds
     *
     * @return int
     */
    public function getDuration(): int
    {
        return (int) (hrtime(true) - $this->durationStart);
    }

    /**
     * Sanitize a string
     *
     * @param string $string
     *
     * @return string
     */
    public static function sanitize(string $string): string
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '-', preg_replace('/\s/', '_', $string));
    }

    /**
     * Convert bytes into a suitable human readable unit
     *
     * @param int $bytes
     * @param int $precision
     *
     * @return string
     */
    public static function convertBytes(int $bytes, int $precision = 2): string
    {
        $units = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $exponent = (int) floor(log($bytes) / log(1024));
        $converted = $bytes / (1024 ** $exponent);

        return sprintf('%s ' . $units[$exponent], round($converted, $precision));
    }

    /**
     * Convert nanoseconds into a suitable human readable unit
     *
     * @param int $nanoseconds
     * @param int $precision
     *
     * @return string
     */
    public static function convertNanoseconds(int $nanoseconds, int $precision = self::TIME_MILLISECONDS): string
    {
        $hours = (int) ($nanoseconds / 3600000000000);
        $hoursRest = $nanoseconds % 3600000000000;

        $minutes = (int) ($hoursRest / 60000000000);
        $minutesRest = $hoursRest % 60000000000;

        $seconds = (int) ($minutesRest / 1000000000);
        $secondsRest = $minutesRest % 1000000000;

        $milliseconds = (int) ($secondsRest / 1000000);
        $millisecondsRest = $secondsRest % 1000000;

        $microseconds = (int) ($millisecondsRest / 1000);
        $microsecondsRest = $millisecondsRest % 1000;

        $time = [];
        if ($precision <= self::TIME_HOURS && $hours) {
            $time[] = $hours . 'h';
        }
        if ($precision <= self::TIME_MINUTES && $minutes) {
            $time[] = $minutes . 'm';
        }
        if ($precision <= self::TIME_SECONDS && $seconds) {
            $time[] = $seconds . 's';
        }
        if ($precision <= self::TIME_MILLISECONDS && $milliseconds) {
            $time[] = $milliseconds . 'ms';
        }
        if ($precision <= self::TIME_MICROSECONDS && $microseconds) {
            $time[] = $microseconds . 'µs';
        }
        if ($precision === self::TIME_NANOSECONDS) {
            $time[] = $microsecondsRest . 'ns';
        }

        return implode(' ', $time);
    }
}
