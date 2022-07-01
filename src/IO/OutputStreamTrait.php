<?php
namespace Orolyn\IO;

use Orolyn\ByteConverter;
use Orolyn\Endian;

trait OutputStreamTrait
{
    abstract public function getEndian(): Endian;

    abstract public function write(string $bytes): void;

    public function writeInt8($value): void
    {
        $this->write(ByteConverter::getBinaryInt8($value));
    }

    public function writeInt16($value): void
    {
        $this->write(ByteConverter::getBinaryInt16($value, $this->getEndian()));
    }

    public function writeInt32($value): void
    {
        $this->write(ByteConverter::getBinaryInt32($value, $this->getEndian()));
    }

    public function writeInt64($value): void
    {
        $this->write(ByteConverter::getBinaryInt64($value, $this->getEndian()));
    }

    public function writeUnsignedInt8($value): void
    {
        $this->write(ByteConverter::getBinaryUnsignedInt8($value, $this->getEndian()));
    }

    public function writeUnsignedInt16($value): void
    {
        $this->write(ByteConverter::getBinaryUnsignedInt16($value, $this->getEndian()));
    }

    public function writeUnsignedInt32($value): void
    {
        $this->write(ByteConverter::getBinaryUnsignedInt32($value, $this->getEndian()));
    }

    public function writeUnsignedInt64($value): void
    {
        $this->write(ByteConverter::getBinaryUnsignedInt64($value, $this->getEndian()));
    }

    public function writeFloat($value): void
    {
        $this->write(ByteConverter::getBinaryFloat($value, $this->getEndian()));
    }

    public function writeDouble($value): void
    {
        $this->write(ByteConverter::getBinaryDouble($value, $this->getEndian()));
    }

    public function writeBool($value): void
    {
        $this->write(ByteConverter::getBinaryBool($value));
    }
}
