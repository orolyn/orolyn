<?php
namespace Orolyn\IO;

use Orolyn\BitConverter;
use Orolyn\Endian;

trait InputStreamTrait
{
    abstract public function getEndian(): Endian;

    abstract public function read(int $length = 1): string;

    public function readNullTerminatedString(): string
    {
        $string = '';

        while ("\x00" !== $byte = $this->read()) {
            $string .= $byte;
        }

        return $string;
    }

    public function readInt8(): int
    {
        return BitConverter::getInt8($this->read());
    }

    public function readInt16(): int
    {
        return BitConverter::getInt16($this->read(2), $this->getEndian());
    }

    public function readInt24(): int
    {
        return BitConverter::getInt24($this->read(3), $this->getEndian());
    }

    public function readInt32(): int
    {
        return BitConverter::getInt32($this->read(4), $this->getEndian());
    }

    public function readInt64(): int
    {
        return BitConverter::getInt64($this->read(8), $this->getEndian());
    }

    public function readUnsignedInt8(): int
    {
        return BitConverter::getUnsignedInt8($this->read());
    }

    public function readUnsignedInt16(): int
    {
        return BitConverter::getUnsignedInt16($this->read(2), $this->getEndian());
    }

    public function readUnsignedInt24(): int
    {
        return BitConverter::getUnsignedInt24($this->read(3), $this->getEndian());
    }

    public function readUnsignedInt32(): int
    {
        return BitConverter::getUnsignedInt32($this->read(4), $this->getEndian());
    }

    public function readUnsignedInt64(): int
    {
        return BitConverter::getUnsignedInt64($this->read(8), $this->getEndian());
    }

    public function readSingle(): float
    {
        return BitConverter::getSingle($this->read(4), $this->getEndian());
    }

    public function readDouble(): float
    {
        return BitConverter::getDouble($this->read(8), $this->getEndian());
    }

    public function readBool(): bool
    {
        return BitConverter::getBool($this->read());
    }
}
