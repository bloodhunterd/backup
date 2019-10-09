<?php
/**
 * This file is part of the Backup project.
 * Visit project at https://github.com/bloodhunterd/backup
 *
 * © BloodhunterD <backup@bloodhunterd.com> | 2019
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Backup;

use Backup\Interfaces\Compressible;

/**
 * Class Directory
 *
 * @author BloodhunterD
 *
 * @package Backup
 */
class Directory implements Compressible
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $target;

    /**
     * Directory constructor
     *
     * @param object $directory
     */
    public function __construct(object $directory)
    {
        $this->setName($directory->name);
        $this->setSource($directory->source);
        $this->setTarget($directory->target);
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
     * Get name
     *
     * @return string
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
     * Get source
     *
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * Set target
     *
     * @param string|null $path
     */
    public function setTarget(string $path = null): void
    {
        $this->target = $path;
    }

    /**
     * Get target
     *
     * @return string|null
     */
    public function getTarget(): ?string
    {
        return $this->target;
    }

    /**
     * Get archive
     *
     * @return string
     */
    public function getArchive(): string
    {
        return $this->getName() . '.tar.bz2';
    }
}
