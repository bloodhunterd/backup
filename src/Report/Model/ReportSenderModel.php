<?php
/*
 * This file ist part of the Backup project, see https://github.com/bloodhunterd/Backup.
 * © 2021 BloodhunterD <bloodhunterd@bloodhunterd.com>
 */

declare(strict_types=1);

namespace Backup\Report\Model;

/**
 * Class ReportSenderModel
 *
 * @package Backup\Report\Model
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class ReportSenderModel
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
     * Report Sender Model constructor
     *
     * @param string[] $sender
     */
    public function __construct(array $sender)
    {
        # Optional
        $this->setAddress($sender['address'] ?? $this->address);
        $this->setName($sender['name'] ?? $this->name);
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
}
