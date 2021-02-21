<?php
/*
 * This file ist part of the Backup project, see https://github.com/bloodhunterd/Backup.
 * © 2021 BloodhunterD <bloodhunterd@bloodhunterd.com>
 */

declare(strict_types=1);

namespace Backup;

use Backup\Exception\ToolException;
use Backup\Interfaces\Compressible;
use Monolog\Logger;
use Vection\Component\DI\Annotations\Inject;
use Vection\Component\DI\Traits\AnnotationInjection;

/**
 * Class Tool
 *
 * @package Backup
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
    private Configuration $config;

    /**
     * @var Logger
     * @Inject("Monolog\Logger")
     */
    private Logger $logger;

    /**
     * Set the timezone
     *
     * @param string $timezone
     */
    public function setTimezone(string $timezone): void
    {
        if (date_default_timezone_set($timezone)) {
            $this->logger->info(sprintf('Timezone set to "%s".', $timezone));

            return;
        }

        $this->logger->warning(sprintf(
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
            $this->logger->info(sprintf('Language set to "%s".', $locale));

            return;
        }

        $this->logger->warning(sprintf(
            'The language "%s" is either not supported or installed. Fallback to "%s".',
            $locale,
            $this->setLocale(LC_ALL, '0')
        ));
    }

    /**
     * Create a directory
     *
     * @param string $path
     * @return bool
     */
    public function createDirectory(string $path): bool
    {
        $absolutePath = $this->config->getTargetDirectory() . $path;

        return @mkdir($absolutePath, 0776, true) || is_dir($absolutePath);
    }

    /**
     * Create an archive
     *
     * @param Compressible $compressible
     */
    public function createArchive(Compressible $compressible): void
    {
        $target = $compressible->getArchive();

        $cmd = sprintf(
            'tar -c%sf %s %s',
            $this->getArchiveInfo()['parameter'],
            escapeshellarg(
                $this->config->getTargetDirectory() .
                $compressible->getTarget() .
                DIRECTORY_SEPARATOR .
                $target
            ),
            escapeshellarg($compressible->getSource())
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
                default:
                    $msg = ' Unknown error occurred.';
            }

            $this->logger->error(
                sprintf('Failed to create archive for "%s.%s".', $compressible->getName(), $msg)
            );

            return;
        }

        $this->logger->info(sprintf('Archive "%s" created.', $target));
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
        $logger = $this->logger->withName(LOGGER_CLI);

        $logger->debug(sprintf('Execute command: %s', $command));

        $escapedCommand = preg_replace('/\s+/', ' ', $command);
        if ($escapedCommand === null) {
            throw new ToolException(sprintf('Failed to sanitize command: %s', $command));
        }

        // Replace tabs and line endings with a single whitespace
        exec($escapedCommand . ' 2>&1', $output, $return);

        foreach ($output as $line) {
            $logger->debug($line);
        }

        $logger->debug(sprintf('Return status: %d', $return));

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
     * Get the archive info
     *
     * @return string[]
     */
    private function getArchiveInfo(): array
    {
        switch ($this->config->getCompression()) {
            case 'bzip2':
                $info = [
                    'parameter' => 'j',
                    'suffix' => 'bz2'
                ];
                break;
            case 'gzip':
            default:
                $info = [
                    'parameter' => 'z',
                    'suffix' => 'gz'
                ];
        }

        return $info;
    }

    /**
     * Get the archive suffix
     *
     * @return string
     */
    public function getArchiveSuffix(): string
    {
        return $this->getArchiveInfo()['suffix'];
    }

    /**
     * Sanitize a string
     *
     * @param string $string
     * @return string
     * @throws ToolException
     */
    public static function sanitize(string $string): string
    {
        $sanitized = preg_replace('/\s/', '_', $string);
        if ($sanitized === null) {
            throw new ToolException(sprintf('Failed to sanitize string: %s', $string));
        }

        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '-', $sanitized);
        if ($sanitized === null) {
            throw new ToolException(sprintf('Failed to sanitize string: %s', $string));
        }

        return $sanitized;
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
