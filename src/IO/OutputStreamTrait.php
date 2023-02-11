<?php
namespace Orolyn\IO;

use Orolyn\ByteConverter;
use Orolyn\Endian;

trait OutputStreamTrait
{
    abstract public function getEndian(): Endian;

    abstract public function write(string $bytes): void;

    public function writeNullTerminated(string $bytes): void
    {
        $this->write($bytes . "\x00");
    }

    public function writeInt8(int $value): void
    {
        $this->write(ByteConverter::getBinaryInt8($value));
    }

    public function writeInt16(int $value): void
    {
        $this->write(ByteConverter::getBinaryInt16($value, $this->getEndian()));
    }

    public function writeInt24(int $value): void
    {
        $this->write(ByteConverter::getBinaryInt24($value, $this->getEndian()));
    }

    public function writeInt32(int $value): void
    {
        $this->write(ByteConverter::getBinaryInt32($value, $this->getEndian()));
    }

    public function writeInt64(int $value): void
    {
        $this->write(ByteConverter::getBinaryInt64($value, $this->getEndian()));
    }

    public function writeUnsignedInt8(int $value): void
    {
        $this->write(ByteConverter::getBinaryUnsignedInt8($value, $this->getEndian()));
    }

    public function writeUnsignedInt16(int $value): void
    {
        $this->write(ByteConverter::getBinaryUnsignedInt16($value, $this->getEndian()));
    }

    public function writeUnsignedInt24(int $value): void
    {
        $this->write(ByteConverter::getBinaryUnsignedInt24($value, $this->getEndian()));
    }

    public function writeUnsignedInt32(int $value): void
    {
        $this->write(ByteConverter::getBinaryUnsignedInt32($value, $this->getEndian()));
    }

    public function writeUnsignedInt64(int $value): void
    {
        $this->write(ByteConverter::getBinaryUnsignedInt64($value, $this->getEndian()));
    }

    public function writeFloat(float $value): void
    {
        $this->write(ByteConverter::getBinaryFloat($value, $this->getEndian()));
    }

    public function writeDouble(float $value): void
    {
        $this->write(ByteConverter::getBinaryDouble($value, $this->getEndian()));
    }

    public function writeBool(bool $value): void
    {
        $this->write(ByteConverter::getBinaryBool($value));
    }

    public function writeNull(int $length = 1): void
    {
        $this->write(str_pad('', $length, "\x00"));
    }

    public function flush(): void
    {
    }

    public function setPosition(int $position): void
    {
    }

    public function getPosition(): int
    {
        return 0;
    }

    public function getBytesPending(): int
    {
        return 0;
    }
}
