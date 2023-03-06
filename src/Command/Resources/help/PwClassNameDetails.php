<?php

/*
 * This file is part of the Symfony MakerBundle package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pw\Command\Resources\help;

use Pw\Command\Resources\help\PwStr;

final class PwClassNameDetails
{
    private $fullClassName;
    private $namespacePrefix;
    private $suffix;

    public function __construct(string $fullClassName, string $namespacePrefix, string $suffix = null)
    {
        $this->fullClassName = $fullClassName;
        $this->namespacePrefix = trim($namespacePrefix, '\\');
        $this->suffix = $suffix;
    }

    public function getFullName(): string
    {
        return $this->fullClassName;
    }

    public function getShortName(): string
    {
        return PwStr::getShortClassName($this->fullClassName);
    }

    /**
     * Returns the original class name the user entered (after
     * being cleaned up).
     *
     * For example, assuming the namespace is App\Entity:
     *      App\Entity\Admin\User => Admin\User
     */
    public function getRelativeName(): string
    {
        return str_replace($this->namespacePrefix.'\\', '', $this->fullClassName);
    }

    public function getRelativeNameWithoutSuffix(): string
    {
        return PwStr::removeSuffix($this->getRelativeName(), $this->suffix);
    }
}
