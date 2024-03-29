<?php
namespace Orolyn\IO;

use Orolyn\Endian;

interface IOutputStream
{
    public function getEndian(): Endian;

    public function setEndian(Endian $type): void;

    public function setPosition(int $position): void;

    public function getPosition(): int;

    public function getBytesPending(): int;

    /**
     * Writes $bytes from the IOutputStream::getPosition() position.
     * If the position is greater than IOutputStream::getLength() then null is written a number of times equal to the
     * position minus the length before the $bytes are written.
     *
     * @param string $bytes
     */
    public function write(string $bytes): void;

    public function writeInt8(int $value): void;

    public function writeInt16(int$value): void;

    public function writeInt24(int$value): void;

    public function writeInt32(int$value): void;

    public function writeInt64(int $value): void;

    public function writeUnsignedInt8(int$value): void;

    public function writeUnsignedInt16(int$value): void;

    public function writeUnsignedInt24(int$value): void;

    public function writeUnsignedInt32(int$value): void;

    public function writeUnsignedInt64(int$value): void;

    public function writeFloat(float $value): void;

    public function writeDouble(float$value): void;

    public function writeBool(bool $value): void;

    public function writeNull(int $length = 1): void;

    public function flush(): void;
}
