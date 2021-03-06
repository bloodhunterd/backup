<?php
/*
 * This file ist part of the Backup project, see https://github.com/bloodhunterd/Backup.
 * © 2021 BloodhunterD <bloodhunterd@bloodhunterd.com>
 */

declare(strict_types=1);

namespace Backup\Agent\Model;

use Backup\Interfaces\Compressible;

/**
 * Class DirectoryModel
 *
 * @package Backup\Agent\Model
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class DirectoryModel implements Compressible
{

    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $archive;

    /**
     * @var string
     */
    private string $source;

    /**
     * @var string
     */
    private string $target;

    /**
     * @var string[]
     */
    private array $commands;

    /**
     * @var bool
     */
    private bool $disabled = false;

    /**
     * Directory Model constructor
     *
     * @param mixed[] $directory
     */
    public function __construct(array $directory)
    {
        # Required
        $this->setName($directory['name']);
        $this->setSource($directory['source']);

        # Optional
        $this->setTarget($directory['target'] ?? '');
        $this->setCommands($directory['commands'] ?? []);

        if (isset($directory['disabled']) && $directory['disabled']) {
            $this->disable();
        }
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set source
     *
     * @param string $path
     */
    public function setSource(string $path): void
    {
        $this->source = $path;
    }

    /**
     * @inheritDoc
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * Set target
     *
     * @param string $path
     */
    public function setTarget(string $path): void
    {
        $this->target = $path;
    }

    /**
     * @inheritDoc
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * Set the archive file name
     *
     * @param string $name
     */
    public function setArchive(string $name): void
    {
        $this->archive = $name;
    }

    /**
     * Get the archive file name
     *
     * @return string
     */
    public function getArchive(): string
    {
        return $this->archive;
    }

    /**
     * Set commands
     *
     * @param string[] $commands
     */
    public function setCommands(array $commands): void
    {
        $this->commands = $commands;
    }

    /**
     * Get command to execute before backup process starts
     *
     * @return string|null
     */
    public function getCommandBefore(): ?string
    {
        return $this->commands['before'] ?? null;
    }

    /**
     * Get command to execute after backup process ended
     *
     * @return string|null
     */
    public function getCommandAfter(): ?string
    {
        return $this->commands['after'] ?? null;
    }

    /**
     * Disable
     */
    public function disable(): void
    {
        $this->disabled = true;
    }

    /**
     * Is disabled
     *
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->disabled;
    }
}
