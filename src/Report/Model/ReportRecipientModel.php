<?php
/*
 * This file ist part of the Backup project, see https://github.com/bloodhunterd/Backup.
 * © 2021 BloodhunterD <bloodhunterd@bloodhunterd.com>
 */

declare(strict_types=1);

namespace Backup\Report\Model;

/**
 * Class ReportRecipientModel
 *
 * @package Backup\Report\Model
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class ReportRecipientModel
{

    /**
     * @var string
     */
    private string $address = '';

    /**
     * @var string
     */
    private string $name = '';

    /**
     * @var string
     */
    private string $type = 'to';

    /**
     * Report Recipient Model constructor
     *
     * @param string[] $recipient
     */
    public function __construct(array $recipient)
    {
        # Optional
        $this->setAddress($recipient['address'] ?? $this->address);
        $this->setName($recipient['name'] ?? $this->name);
        $this->setType($recipient['type'] ?? $this->type);
    }

    /**
     * Set the address
     *
     * @param string $address
     */
    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    /**
     * Get the address
     *
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * Set the name
     *
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Get the name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the type
     *
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * Get the type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
