<?php
namespace Orolyn\IO;

use Orolyn\Endian;

interface IInputStream
{
    public function getEndian(): Endian;

    public function setEndian(Endian $type): void;

    public function setPosition(int $position): void;

    public function getPosition(): int;

    public function isEndOfStream(): bool;

    public function getLength(): int;

    public function getBytesAvailable(): int;

    public function peek(int $length = 1): ?string;

    /**
     * Reads specified length of bytes from the stream.
     * If the buffer does not contain enough bytes to fill the length, then this method will block until the require
     * bytes become available.
     *
     * If the stream is closed during this call then this method will return the request bytes from the buffer if
     * there are enough, however if there are insufficient bytes available, then the remainder of the buffer will
     * be returned instead.
     *
     * If the stream is already closed and the buffer is empty, this method will throw an exception.
     *
     * @param int $length
     * @return string
     */
    public function read(int $length = 1): ?string;

    public function readInt8(): int;

    public function readInt16(): int;

    public function readInt32(): int;

    public function readInt64(): int;

    public function readUnsignedInt8(): int;

    public function readUnsignedInt16(): int;

    public function readUnsignedInt32(): int;

    public function readUnsignedInt64(): int;

    public function readFloat(): float;

    public function readDouble(): float;

    public function readBool(): bool;
}
