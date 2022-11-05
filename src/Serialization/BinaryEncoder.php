<?php

namespace Orolyn\Serialization;

use Closure;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;
use Orolyn\Reflection;
use RuntimeException;

class BinaryEncoder extends Encoder
{
    protected function decodeOpaque(IInputStream $stream, int $length): string
    {
        return $stream->read($length);
    }

    protected function decodeUnsignedInt8(IInputStream $stream): int
    {
        return $stream->readUnsignedInt8();
    }

    protected function decodeUnsignedInt16(IInputStream $stream): int
    {
        return $stream->readUnsignedInt16();
    }

    protected function decodeUnsignedInt24(IInputStream $stream): int
    {
        return $stream->readUnsignedInt24();
    }

    protected function decodeUnsignedInt32(IInputStream $stream): int
    {
        return $stream->readUnsignedInt32();
    }

    protected function decodeUnsignedInt64(IInputStream $stream): int
    {
        return $stream->readUnsignedInt64();
    }

    protected function decodeInt8(IInputStream $stream): int
    {
        return $stream->readInt8();
    }

    protected function decodeInt16(IInputStream $stream): int
    {
        return $stream->readInt16();
    }

    protected function decodeInt24(IInputStream $stream): int
    {
        return $stream->readInt24();
    }

    protected function decodeInt32(IInputStream $stream): int
    {
        return $stream->readInt32();
    }

    protected function decodeInt64(IInputStream $stream): int
    {
        return $stream->readInt64();
    }

    protected function decodeSingle(IInputStream $stream): float
    {
        return $stream->readSingle();
    }

    protected function decodeDouble(IInputStream $stream): float
    {
        return $stream->readDouble();
    }

    protected function decodeBool(IInputStream $stream): bool
    {
        return $stream->readBool();
    }

    protected function encodeOpaque(IOutputStream $stream, string $value): void
    {
        $stream->write($value);
    }

    protected function encodeUnsignedInt8(IOutputStream $stream, int $value): void
    {
        $stream->writeUnsignedInt8($value);
    }

    protected function encodeUnsignedInt16(IOutputStream $stream, int $value): void
    {
        $stream->writeUnsignedInt16($value);
    }

    protected function encodeUnsignedInt24(IOutputStream $stream, int $value): void
    {
        $stream->writeUnsignedInt24($value);
    }

    protected function encodeUnsignedInt32(IOutputStream $stream, int $value): void
    {
        $stream->writeUnsignedInt32($value);
    }

    protected function encodeUnsignedInt64(IOutputStream $stream, int $value): void
    {
        $stream->writeUnsignedInt64($value);
    }

    protected function encodeInt8(IOutputStream $stream, int $value): void
    {
        $stream->writeInt8($value);
    }

    protected function encodeInt16(IOutputStream $stream, int $value): void
    {
        $stream->writeInt16($value);
    }

    protected function encodeInt24(IOutputStream $stream, int $value): void
    {
        $stream->writeInt24($value);
    }

    protected function encodeInt32(IOutputStream $stream, int $value): void
    {
        $stream->writeInt32($value);
    }

    protected function encodeInt64(IOutputStream $stream, int $value): void
    {
        $stream->writeInt64($value);
    }

    protected function encodeSingle(IOutputStream $stream, float $value): void
    {
        $stream->writeSingle($value);
    }

    protected function encodeDouble(IOutputStream $stream, float $value): void
    {
        $stream->writeDouble($value);
    }

    protected function encodeBool(IOutputStream $stream, bool $value): void
    {
        $stream->writeBool($value);
    }
}
