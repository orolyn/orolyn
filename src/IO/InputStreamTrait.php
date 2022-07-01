<?php
namespace Orolyn\IO;

use Orolyn\ByteConverter;
use Orolyn\Endian;

trait InputStreamTrait
{
    abstract public function getEndian(): Endian;

    abstract public function read(int $length = 1): ?string;

    public function readInt8(): int
    {
        return ByteConverter::getInt8($this->read());
    }

    public function readInt16(): int
    {
        return ByteConverter::getInt16($this->read(2), $this->getEndian());
    }

    public function readInt32(): int
    {
        return ByteConverter::getInt32($this->read(4), $this->getEndian());
    }

    public function readInt64(): int
    {
        return ByteConverter::getInt64($this->read(8), $this->getEndian());
    }

    public function readUnsignedInt8(): int
    {
        return ByteConverter::getUnsignedInt8($this->read());
    }

    public function readUnsignedInt16(): int
    {
        return ByteConverter::getUnsignedInt16($this->read(2), $this->getEndian());
    }

    public function readUnsignedInt32(): int
    {
        return ByteConverter::getUnsignedInt32($this->read(4), $this->getEndian());
    }

    public function readUnsignedInt64(): int
    {
        return ByteConverter::getUnsignedInt64($this->read(8), $this->getEndian());
    }

    public function readFloat(): float
    {
        return ByteConverter::getFloat($this->read(4), $this->getEndian());
    }

    public function readDouble(): float
    {
        return ByteConverter::getDouble($this->read(8), $this->getEndian());
    }

    public function readBool(): bool
    {
        return ByteConverter::getBool($this->read());
    }
}
