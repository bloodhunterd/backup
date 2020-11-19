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

namespace Backup\Agent\Model;

use Backup\Interfaces\Compressible;

/**
 * Class Directory Model
 *
 * @package Backup\Agent\Model
 *
 * @author BloodhunterD <bloodhunterd@bloodhunterd.com>
 */
class DirectoryModel implements Compressible
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $archive;

    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $target;

    /**
     * @var string[]
     */
    private $commands;

    /**
     * @var bool
     */
    private $disabled = false;

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

        if (isset($directory['disabled']) && $directory['disabled'] === 'yes') {
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
     * Set archive
     *
     * @param string $name
     */
    public function setArchive(string $name): void
    {
        $this->archive = $name . '.tar.bz2';
    }

    /**
     * Get archive
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
