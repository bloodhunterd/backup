<?php

/*
 * @package    Backup
 * @author     BloodhunterD <bloodhunterd@bloodhunterd.com>
 * @link       https://github.com/bloodhunterd
 * @copyright  © 2020 BloodhunterD
 */

declare(strict_types=1);

namespace Backup\Report\Model;

/**
 * Class Report Recipient Model
 *
 * @package Backup\Report\Model
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class ReportRecipientModel
{

    /**
     * @var string
     */
    private $address = '';

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $type = 'to';

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
