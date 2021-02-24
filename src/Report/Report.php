<?php
/*
 * This file ist part of the Backup project, see https://github.com/bloodhunterd/Backup.
 * © 2021 BloodhunterD <bloodhunterd@bloodhunterd.com>
 */

declare(strict_types=1);

namespace Backup\Report;

use Backup\Configuration;
use Backup\Exception\BackupException;
use Backup\Interfaces\Backup;
use Backup\Report\Model\ReportRecipientModel;
use Backup\Report\Model\ReportSenderModel;
use Backup\Tool;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use Vection\Component\DI\Annotations\Inject;
use Vection\Component\DI\Traits\AnnotationInjection;

/**
 * Class Report
 *
 * @package Backup\Report
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class Report
{
    use AnnotationInjection;

    private const MAIL_TO = 'to';
    private const MAIL_CC = 'cc';
    private const MAIL_BCC = 'bcc';

    public const RESULT_OK = 'OK';
    public const RESULT_INFO = 'INFO';
    public const RESULT_WARNING = 'WARNING';
    public const RESULT_ERROR = 'ERROR';

    private const COLORS = [
        self::RESULT_INFO => '#2962FF',
        self::RESULT_OK => '#00C853',
        self::RESULT_WARNING => '#FFAB00',
        self::RESULT_ERROR => '#D50000',
        'DEFAULT' => '#212121'
    ];

    private const EMOJIS = [
        Backup::TYPE_DIRECTORY => '📁',
        Backup::TYPE_DATABASE => '💿',
        Backup::TYPE_SERVER => '🖥',
        'DEFAULT' => '❓'
    ];

    /**
     * @var ReportSenderModel
     */
    private ReportSenderModel $sender;

    /**
     * @var string
     */
    private string $subject;

    /**
     * @var ReportRecipientModel[]
     */
    private array $recipients;

    /**
     * @var mixed[]
     */
    private array $tasks = [];

    /**
     * @var Configuration
     * @Inject("Backup\Configuration")
     */
    private Configuration $config;

    /**
     * @var Tool
     * @Inject("Backup\Tool")
     */
    private Tool $tool;

    /**
     * Set the sender
     *
     * @param ReportSenderModel $sender
     */
    public function setSender(ReportSenderModel $sender): void
    {
        $this->sender = $sender;
    }

    /**
     * Set the subject
     *
     * @param string $subject
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * Add a recipient
     *
     * @param ReportRecipientModel $recipient
     */
    public function addRecipient(ReportRecipientModel $recipient): void
    {
        $this->recipients[] = $recipient;
    }

    /**
     * Add a backup task status to the report
     *
     * @param string $status
     * @param string $type
     * @param string $message
     * @param object $model
     * @param int|null $fileSize
     * @param int|null $duration
     */
    public function add(
        string $status,
        string $type,
        string $message,
        object $model,
        int $fileSize = null,
        int $duration = null

    ): void
    {
        $this->tasks[] = compact('status', 'type', 'message', 'model', 'fileSize', 'duration');
    }

    /**
     * Send the report
     *
     * @throws BackupException
     */
    public function send(): bool
    {
        $name = $this->sender->getName();
        $address = $this->sender->getAddress();
        $sender = $name ? "{$name} <{$address}>" : $address;

        $headers = [
            'From' => $sender,
            'Reply-To' => $sender,
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html; charset=utf-8',
            'X-Application' => 'Backup by BloodhunterD'
        ];

        $to = [];
        $cc = [];
        $bcc = [];
        foreach ($this->recipients as $recipient) {
            $name = $recipient->getName();
            $address = $recipient->getAddress();
            $address = $name ? "{$name} <{$address}>" : $address;

            switch ($recipient->getType()) {
                default:
                case self::MAIL_TO:
                    $to[] = $address;
                    break;
                case self::MAIL_CC:
                    $cc[] = $address;
                    break;
                case self::MAIL_BCC:
                    $bcc[] = $address;
                    break;
            }
        }

        if ($cc) {
            $headers['Cc'] = implode(', ', $cc);
        }

        if ($bcc) {
            $headers['Bcc'] = implode(', ', $bcc);
        }

        $types = [
            Backup::TYPE_DIRECTORY => 'Directories',
            Backup::TYPE_DATABASE => 'Databases',
            Backup::TYPE_SERVER => 'Servers'
        ];

        $errorOccurred = false;

        $this->tool->setLanguage($this->config->getLanguage());

        $backups = [];
        foreach ($types as $type => $name) {
            $tasks[$type] = array_filter($this->tasks, static function ($task) use ($type) {
                return $task['type'] === $type;
            });

            // Skip if backup type has no tasks
            if (!$tasks[$type]) {
                continue;
            }

            array_walk($tasks[$type], static function (&$task) use (&$errorOccurred) {
                $task['name'] = $task['model']->getName();
                $task['color'] = self::getBackgroundColor($task['status']);
                $task['fileSize'] = isset($task['fileSize']) ? Tool::convertBytes($task['fileSize']) : '';
                $task['duration'] = isset($task['duration']) ? Tool::convertNanoseconds($task['duration']) : '';

                // Mark error messages for additional information
                if ($task['status'] === self::RESULT_ERROR) {
                    $task['message'] .= ' *';

                    $errorOccurred = true;
                }

                // Remove model, it's not needed
                unset($task['model']);

                return $task;
            });

            $backups[$type]['name'] = $name;
            $backups[$type]['emoji'] = self::getEmoji($type);
            $backups[$type]['tasks'] = $tasks[$type];
        }

        $twig = new Environment(new FilesystemLoader(RES_DIR));

        $dateTime = strftime('%x %X');

        try {
            $mail = $twig->render('report.twig', compact('backups', 'errorOccurred', 'dateTime'));
        } catch (LoaderError | RuntimeError | SyntaxError $e) {
            throw new BackupException('Failed to render mail template.', 0, $e->getPrevious());
        }

        # The subject is a header and headers are only allowed to contain ASCII chars,
        # so we need to encode the subject like described in RFC 1342
        return mail(
            implode(', ', $to),
            '=?UTF-8?B?' . base64_encode($this->subject) . '?=',
            $mail,
            $headers
        );
    }

    /**
     * Get background color by status
     *
     * @param string $status
     * @return string
     */
    private static function getBackgroundColor(string $status): string
    {
        return self::COLORS[$status] ?? self::COLORS['DEFAULT'];
    }

    /**
     * Get emoji by type
     *
     * @param string $type
     * @return string
     */
    private static function getEmoji(string $type): string
    {
        return self::EMOJIS[$type] ?? self::EMOJIS['DEFAULT'];
    }
}
