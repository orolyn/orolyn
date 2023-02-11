<?php

namespace Orolyn\Data\Mysql\Protocol;

use Orolyn\ArgumentOutOfRangeException;
use Orolyn\IO\Binary;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;

class LengthEncoded
{
    public static function encodeLengthEncodedInteger(IOutputStream $stream, int $value): void
    {
        if ($value < 0) {
            throw new ArgumentOutOfRangeException('value');
        }

        if ($value < 251) {
            $stream->writeUnsignedInt8($value);
        } elseif ($value >= 251 && $value < 2**16) {
            $stream->write("\xFC");
            $stream->writeUnsignedInt16($value);
        } elseif ($value >= 2**16 && $value < 2**24) {
            $stream->write("\xFD");
            $stream->writeUnsignedInt24($value);
        } else {
            $stream->write("\xFE");
            $stream->writeInt64($value); // Cannot write unsigned
        }
    }

    public static function decodeLengthEncodedInteger(IInputStream $stream): int
    {
        $value = $stream->readUnsignedInt8();

        if ($value < 251) {
            return $value;
        }

        return match ($value) {
            0xFC => $stream->readUnsignedInt16(),
            0xFD => $stream->readUnsignedInt32(),
            default => $stream->readUnsignedInt64(),
        };
    }

    public static function encodeLengthEncodedString(IOutputStream $stream, string $value): void
    {
        self::encodeLengthEncodedInteger($stream, Binary::getLength($value));
        $stream->write($value);
    }

    public static function decodeLengthEncodedString(IInputStream $stream): string
    {
        return $stream->read(self::decodeLengthEncodedInteger($stream));
    }
}
