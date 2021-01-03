<?php

/*
 * @package    Backup
 * @author     BloodhunterD <bloodhunterd@bloodhunterd.com>
 * @link       https://github.com/bloodhunterd
 * @copyright  © 2020 BloodhunterD
 */

declare(strict_types=1);

namespace Backup;

use Backup\Exception\ConfigurationException;
use Backup\Report\Model\ReportRecipientModel;
use Backup\Report\Model\ReportSenderModel;
use Vection\Component\DI\Annotations\Inject;
use Vection\Component\DI\Traits\AnnotationInjection;
use Vection\Component\Validator\Schema\Schema;
use Vection\Component\Validator\Schema\SchemaValidator;
use Vection\Contracts\Validator\Schema\PropertyExceptionInterface;
use Vection\Contracts\Validator\Schema\SchemaExceptionInterface;

/**
 * Class Configuration
 *
 * @package Backup
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class Configuration
{
    use AnnotationInjection;

    /**
     * @var string
     */
    private $path;

    /**
     * @var mixed[]
     */
    private $settings;

    /**
     * @var Logger
     * @Inject("Backup\Logger")
     */
    private $logger;

    /**
     * Set the path to the configuration file
     *
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * Load the settings from configuration file
     *
     * @throws ConfigurationException
     */
    public function load(): void
    {
        $validator = new SchemaValidator(new Schema(RES_DIR . DIRECTORY_SEPARATOR . 'config.schema.json'));

        $json = file_get_contents($this->path);
        if ($json === false) {
            throw new ConfigurationException(sprintf('Failed to load the configuration from "%s".', $this->path));
        }

        try {
            $validator->validateJsonString($json);
        } catch (PropertyExceptionInterface | SchemaExceptionInterface $e) {
            $msg = 'The configuration is invalid. %s';

            throw new ConfigurationException(sprintf($msg, $e->getMessage()));
        }

        $this->settings = json_decode($json, true);

        $this->logger->use('app')->info('Configuration loaded.');
    }

    /**
     * Get the timezone
     *
     * @return string
     */
    public function getTimezone(): string
    {
        return $this->settings['timezone'] ?? '';
    }

    /**
     * Get the language
     *
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->settings['language'] ?? '';
    }

    /**
     * Get the compression method
     *
     * @return string
     */
    public function getCompression(): string
    {
        return $this->settings['compression'] ?? '';
    }

    /**
     * Is debug enabled
     *
     * @return bool
     */
    public function isDebugEnabled(): bool
    {
        return isset($this->settings['debug']) && $this->settings['debug'] === 'yes';
    }

    /**
     * Is debug disabled
     *
     * @return bool
     */
    public function isDebugDisabled(): bool
    {
        return !$this->isDebugEnabled();
    }

    /**
     * Get the mode
     *
     * @return string
     */
    public function getMode(): string
    {
        return $this->settings['mode'];
    }

    /**
     * Get the report sender
     *
     * @return ReportSenderModel
     */
    public function getReportSender(): ReportSenderModel
    {
        return new ReportSenderModel($this->settings['report']['sender']);
    }

    /**
     * Get report subject
     *
     * @return string
     */
    public function getReportSubject(): string
    {
        return $this->settings['report']['subject'] ?? '';
    }

    /**
     * Get the report recipients
     *
     * @return ReportRecipientModel[]
     */
    public function getReportRecipients(): array
    {
        $recipients = $this->settings['report']['recipients'] ?? [];

        $recipientModels = [];
        foreach ($recipients as $recipient) {

            $recipientModels[] = new ReportRecipientModel($recipient);
        }

        return $recipientModels;
    }

    /**
     * Is report enabled
     *
     * @return bool
     */
    public function isReportEnabled(): bool
    {
        return !$this->isReportDisabled();
    }

    /**
     * Is report disabled
     *
     * @return bool
     */
    public function isReportDisabled(): bool
    {
        return isset($this->settings['report']['disabled']) && $this->settings['report']['disabled'] === 'yes';
    }

    /**
     * Get the sources
     *
     * @return mixed[]
     */
    public function getSources(): array
    {
        return $this->settings['sources'];
    }

    /**
     * Get the directories
     *
     * @return mixed[]
     */
    public function getDirectories(): array
    {
        return $this->getSources()['directories'] ?? [];
    }

    /**
     * Get the databases
     *
     * @return mixed[]
     */
    public function getDatabases(): array
    {
        return $this->getSources()['databases'] ?? [];
    }

    /**
     * Get the servers
     *
     * @return mixed[]
     */
    public function getServers(): array
    {
        return $this->getSources()['servers'] ?? [];
    }

    /**
     * Get the target directory
     *
     * @return string
     */
    public function getTargetDirectory(): string
    {
        return $this->settings['target']['directory'];
    }
}
