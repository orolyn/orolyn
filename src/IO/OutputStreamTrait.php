<?php
namespace Orolyn\IO;

use Orolyn\BitConverter;
use Orolyn\Endian;

trait OutputStreamTrait
{
    abstract public function getEndian(): Endian;

    abstract public function write(string $bytes): void;

    public function writeInt8(int $value): void
    {
        $this->write(BitConverter::getBinaryInt8($value));
    }

    public function writeInt16(int $value): void
    {
        $this->write(BitConverter::getBinaryInt16($value, $this->getEndian()));
    }

    public function writeInt24(int $value): void
    {
        $this->write(BitConverter::getBinaryInt24($value, $this->getEndian()));
    }

    public function writeInt32(int $value): void
    {
        $this->write(BitConverter::getBinaryInt32($value, $this->getEndian()));
    }

    public function writeInt64(int $value): void
    {
        $this->write(BitConverter::getBinaryInt64($value, $this->getEndian()));
    }

    public function writeUnsignedInt8(int $value): void
    {
        $this->write(BitConverter::getBinaryUnsignedInt8($value, $this->getEndian()));
    }

    public function writeUnsignedInt16(int $value): void
    {
        $this->write(BitConverter::getBinaryUnsignedInt16($value, $this->getEndian()));
    }

    public function writeUnsignedInt24(int $value): void
    {
        $this->write(BitConverter::getBinaryUnsignedInt24($value, $this->getEndian()));
    }

    public function writeUnsignedInt32(int $value): void
    {
        $this->write(BitConverter::getBinaryUnsignedInt32($value, $this->getEndian()));
    }

    public function writeUnsignedInt64(int $value): void
    {
        $this->write(BitConverter::getBinaryUnsignedInt64($value, $this->getEndian()));
    }

    public function writeSingle(float $value): void
    {
        $this->write(BitConverter::getBinarySingle($value, $this->getEndian()));
    }

    public function writeDouble(float $value): void
    {
        $this->write(BitConverter::getBinaryDouble($value, $this->getEndian()));
    }

    public function writeBool(bool $value): void
    {
        $this->write(BitConverter::getBinaryBool($value));
    }

    public function flush(): void
    {
    }
}
