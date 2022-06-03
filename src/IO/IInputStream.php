<?php
namespace Orolyn\IO;

use Orolyn\Endian;

interface IInputStream
{
    /**
     * Gets the byte order of multi-byte data types.
     *
     * @return Endian
     */
    public function getEndian(): Endian;

    /**
     * Sets the byte order of multi-byte data types.
     *
     * @param Endian $type
     * @return void
     */
    public function setEndian(Endian $type): void;

    /**
     * Sets the pointer to the provided position within the stream. If the stream does not have determinate length,
     * then it must skip over as many bytes as indicated by the provided position.
     *
     * @param int $position
     * @return void
     */
    public function setPosition(int $position): void;

    /**
     * Return the pointer position within the stream. If the stream does not have determinate length, it must
     * return zero.
     *
     * @return int
     */
    public function getPosition(): int;

    /**
     * Returns true if the stream pointer is at the end of the stream, or if the stream is no longer valid, if
     * this stream does not have determinate length, it must return false so long as it is still valid.
     *
     * @return bool
     */
    public function isEndOfStream(): bool;

    /**
     * The length of total byte in this stream. If this stream does not have determinate length, it must return zero.
     *
     * @return int
     */
    public function getLength(): int;

    /**
     * Get the number of bytes that are readable after the pointer in this stream.
     *
     * @return int
     */
    public function getBytesAvailable(): int;

    /**
     * Reads ahead the indicated number of bytes in the stream without changing the pointer's position in the stream.
     *
     * @param int $length
     * @return string|null
     */
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

    /**
     * Read a single 8 bit integer.
     *
     * @return int
     */
    public function readInt8(): int;

    /**
     * Read a single 16 bit integer.
     *
     * @return int
     */
    public function readInt16(): int;

    /**
     * Read a single 32 bit integer.
     *
     * @return int
     */
    public function readInt32(): int;

    /**
     * Read a single 64 bit integer.
     *
     * @return int
     */
    public function readInt64(): int;

    /**
     * Read a single 8 bit unsigned integer.
     *
     * @return int
     */
    public function readUnsignedInt8(): int;

    /**
     * Read a single 16 bit unsigned integer.
     *
     * @return int
     */
    public function readUnsignedInt16(): int;

    /**
     * Read a single 32 bit unsigned integer.
     *
     * @return int
     */
    public function readUnsignedInt32(): int;

    /**
     * Read a single 64 bit unsigned integer. Maximum is the same as :readInt64()
     *
     * @return int
     */
    public function readUnsignedInt64(): int;

    /**
     * Read a single 32 bit floating point value.
     *
     * @return float
     */
    public function readFloat(): float;

    /**
     * Read a single 64 bit floating point value.
     *
     * @return float
     */
    public function readDouble(): float;

    /**
     * Read the next byte as a boolean, int(0) for false, all else is true.
     *
     * @return bool
     */
    public function readBool(): bool;
}
