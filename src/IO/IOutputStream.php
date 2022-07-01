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

    public function writeInt8($value): void;

    public function writeInt16($value): void;

    public function writeInt32($value): void;

    public function writeUnsignedInt8($value): void;

    public function writeUnsignedInt16($value): void;

    public function writeUnsignedInt32($value): void;

    public function writeUnsignedInt64($value): void;

    public function writeFloat($value): void;

    public function writeDouble($value): void;

    public function writeBool($value): void;
}
