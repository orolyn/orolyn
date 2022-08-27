<?php

namespace Orolyn\Security\Cryptography;

use Orolyn\InvalidOperationException;
use Orolyn\IO\IInputStream;

class Hash
{
    /**
     * @var string|null
     */
    private ?string $hex = null;

    /**
     * @param string $bytes
     */
    public function __construct(
        private string $bytes
    ) {
    }

    /**
     * @return string
     */
    public function getHexadecimalString(): string
    {
        return $this->hex ?? ($this->hex = bin2hex($this->bytes));
    }

    /**
     * @return string
     */
    public function getBytes(): string
    {
        return $this->bytes;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getBytes();
    }
}
