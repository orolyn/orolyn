<?php

namespace Orolyn;



final class BitConverter
{
    public const MAX_INT_8  = 0x7F;
    public const MAX_INT_16 = 0x7FFF;
    public const MAX_INT_24 = 0x7FFFFF;
    public const MAX_INT_32 = 0x7FFFFFFF;
    public const MAX_INT_40 = 0x7FFFFFFFFF;
    public const MAX_INT_48 = 0x7FFFFFFFFFFF;
    public const MAX_INT_56 = 0x7FFFFFFFFFFFFF;
    public const MAX_INT_64 = 0x7FFFFFFFFFFFFFFF;

    public const MAX_UNSIGNED_INT_8  = 0xFF;
    public const MAX_UNSIGNED_INT_16 = 0xFFFF;
    public const MAX_UNSIGNED_INT_24 = 0xFFFFFF;
    public const MAX_UNSIGNED_INT_32 = 0xFFFFFFFF;
    public const MAX_UNSIGNED_INT_40 = 0xFFFFFFFFFF;
    public const MAX_UNSIGNED_INT_48 = 0xFFFFFFFFFFFF;
    public const MAX_UNSIGNED_INT_56 = 0xFFFFFFFFFFFFFF;
    public const MAX_UNSIGNED_INT_64 = 0xFFFFFFFFFFFFFFFF;

    public const MIN_INT_8  = -(self::MAX_INT_8)-1;
    public const MIN_INT_16 = -(self::MAX_INT_16)-1;
    public const MIN_INT_24 = -(self::MAX_INT_24)-1;
    public const MIN_INT_32 = -(self::MAX_INT_32)-1;
    public const MIN_INT_40 = -(self::MAX_INT_40)-1;
    public const MIN_INT_48 = -(self::MAX_INT_48)-1;
    public const MIN_INT_56 = -(self::MAX_INT_56)-1;
    public const MIN_INT_64 = -(self::MAX_INT_64)-1;

    private static Endian $defaultEndian;

    public static function getInt8(string $value): int
    {
        if (is_int($value)) {
            return ($value & 0xFF) | ((($value & 0xFF) >> 7) * (((2 ** 56) - 1) << 8));
        }

        return (int)unpack('c', $value)[1];
    }

    public static function getInt16(string $value, ?Endian $endian = null): int
    {
        if (($endian ?? self::$defaultEndian) === Endian::BigEndian) {
            $int = unpack('n', $value)[1];
        } else {
            $int = unpack('v', $value)[1];
        }

        if ($int > self::MAX_INT_16) {
            return self::MIN_INT_16 + ($int - self::MAX_INT_16) - 1;
        }

        return $int;
    }

    public static function getInt24(string $value, ?Endian $endian = null): int
    {
        if (self::getEndian($endian) === Endian::BigEndian) {
            $int = (ord($value[0]) << 16) |  (ord($value[1]) << 8) | ord($value[2]);
        } else {
            $int = (ord($value[2]) << 16) |  (ord($value[1]) << 8) | ord($value[0]);
        }

        if ($int > self::MAX_INT_24) {
            return self::MIN_INT_24 + ($int - self::MAX_INT_24) - 1;
        }

        return $int;
    }

    public static function getInt32(string $value, ?Endian $endian = null): int
    {
        if (($endian ?? self::$defaultEndian) === Endian::BigEndian) {
            $int = unpack('N', $value)[1];
        } else {
            $int = unpack('V', $value)[1];
        }

        if ($int > self::MAX_INT_32) {
            return self::MIN_INT_32 + ($int - self::MAX_INT_32) - 1;
        }

        return $int;
    }

    public static function getInt40(string $value, ?Endian $endian = null): int
    {
        if (($endian ?? self::$defaultEndian) === Endian::BigEndian) {
            $int =
                (ord($value[0]) << 32) | (ord($value[1]) << 24) | (ord($value[2]) << 16) |
                (ord($value[3]) << 8) | ord($value[4]);
        } else {
            $int =
                (ord($value[3]) << 32) | (ord($value[3]) << 24) | (ord($value[2]) << 16) |
                (ord($value[1]) << 8) | ord($value[0]);
        }

        if ($int > self::MAX_INT_40) {
            return self::MIN_INT_40 + ($int - self::MAX_INT_40) - 1;
        }

        return $int;
    }

    public static function getInt48(string $value, ?Endian $endian = null): int
    {
        if (($endian ?? self::$defaultEndian) === Endian::BigEndian) {
            $int =
                (ord($value[0]) << 40) | (ord($value[1]) << 32) | (ord($value[2]) << 24) |
                (ord($value[3]) << 16) | (ord($value[4]) << 8) | ord($value[5]);
        } else {
            $int =
                (ord($value[5]) << 40) | (ord($value[4]) << 32) | (ord($value[3]) << 24) |
                (ord($value[2]) << 16) | (ord($value[1]) << 8) | ord($value[0]);
        }

        if ($int > self::MAX_INT_48) {
            return self::MIN_INT_48 + ($int - self::MAX_INT_48) - 1;
        }

        return $int;
    }

    public static function getInt56(string $value, ?Endian $endian = null): int
    {
        if (($endian ?? self::$defaultEndian) === Endian::BigEndian) {
            $int =
                (ord($value[0]) << 48) | (ord($value[1]) << 40) | (ord($value[2]) << 32) |
                (ord($value[3]) << 24) | (ord($value[4]) << 16) | (ord($value[4]) << 8) | ord($value[5]);
        } else {
            $int =
                (ord($value[6]) << 48) | (ord($value[5]) << 40) | (ord($value[4]) << 32) |
                (ord($value[3]) << 24) | (ord($value[2]) << 16) | (ord($value[1]) << 8) | ord($value[0]);
        }

        if ($int > self::MAX_INT_56) {
            return self::MIN_INT_56 + ($int - self::MAX_INT_56) - 1;
        }

        return $int;
    }

    public static function getInt64(string $value, ?Endian $endian = null): int
    {
        return unpack('q', ($endian ?? self::$defaultEndian) === self::$defaultEndian ? $value : strrev($value))[1];
    }

    public static function getUnsignedInt8(string|int $value): int
    {
        if (is_int($value)) {
            return $value & 0xFF;
        }

        return ord($value[0]);
    }

    public static function getUnsignedInt16(string $value, ?Endian $endian = null): int
    {
        return (int)unpack('S', self::getEndian($endian)->convert($value))[1];
    }

    public static function getUnsignedInt24(string $value, ?Endian $endian = null): int
    {
        if (self::getEndian($endian) === Endian::BigEndian) {
            return (ord($value[0]) << 16) |  (ord($value[1]) << 8) | ord($value[2]);
        } else {
            return (ord($value[2]) << 16) |  (ord($value[1]) << 8) | ord($value[0]);
        }
    }

    public static function getUnsignedInt32(string $value, ?Endian $endian = null): int
    {
        return (int)unpack('L', self::getEndian($endian)->convert($value))[1];
    }

    public static function getUnsignedInt64(string $value, ?Endian $endian = null): int
    {
        return (int)unpack('Q', self::getEndian($endian)->convert($value))[1];
    }

    public static function getSingle(string $value, ?Endian $endian = null): float
    {
        return round(unpack('f', self::getEndian($endian)->convert($value))[1], 7);
    }

    public static function getDouble(string $value, ?Endian $endian = null): float
    {
        return (float)unpack('d', self::getEndian($endian)->convert($value))[1];
    }

    public static function getBool(string $value): bool
    {
        return "\x00" !== $value[0];
    }

    public static function getBinaryInt8(int $value): string
    {
        return pack('c', $value);
    }

    public static function getBinaryInt16(int $value, ?Endian $endian = null): string
    {
        return self::getEndian($endian)->convert(pack('s', $value));
    }

    public static function getBinaryInt24(int $value, ?Endian $endian = null): string
    {
        if (self::getEndian($endian) === Endian::BigEndian) {
            return chr(($value >> 16) & 0xFF) . chr(($value >> 8) & 0xFF) . chr($value & 0xFF);
        } else {
            return chr($value & 0xFF) . chr(($value >> 8) & 0xFF) . chr(($value >> 16) & 0xFF);
        }
    }

    public static function getBinaryInt24b(int $value, ?Endian $endian = null): string
    {
        if (self::getEndian($endian) === Endian::BigEndian) {
            return pack('nc', $value >> 8 & 0xFFFF, $value & 0xFF);
        } else {
            return pack('cv', $value & 0xFF, $value >> 8 & 0xFFFF);
        }
    }

    public static function getBinaryInt32(int $value, ?Endian $endian = null): string
    {
        return self::getEndian($endian)->convert(pack('l', $value));
    }

    public static function getBinaryInt64(int $value, ?Endian $endian = null): string
    {
        return self::getEndian($endian)->convert(pack('q', $value));
    }

    public static function getBinaryUnsignedInt8(int $value, ?Endian $endian = null): string
    {
        return chr($value);
    }

    public static function getBinaryUnsignedInt16(int $value, ?Endian $endian = null): string
    {
        return self::getEndian($endian)->convert(pack('S', $value));
    }

    public static function getBinaryUnsignedInt24(int $value, ?Endian $endian = null): string
    {
        if ($endian === Endian::BigEndian) {
            return chr(($value >> 16) & 0xFF) . chr(($value >> 8) & 0xFF) . chr($value & 0xFF);
        } else {
            return chr($value & 0xFF) . chr(($value >> 8) & 0xFF) . chr(($value >> 16) & 0xFF);
        }
    }

    public static function getBinaryUnsignedInt24b(int $value, ?Endian $endian = null): string
    {
        if ($endian === Endian::BigEndian) {
            return pack('nC', $value >> 8 & 0xFFFF, $value & 0xFF);
        } else {
            return pack('CS', $value & 0xFF, $value >> 8 & 0xFFFF);
        }
    }

    public static function getBinaryUnsignedInt32(int $value, ?Endian $endian = null): string
    {
        return self::getEndian($endian)->convert(pack('L', $value));
    }

    public static function getBinaryUnsignedInt64(int $value, ?Endian $endian = null): string
    {
        return pack('Q', $value);
    }

    public static function getBinaryUnsignedInt64b(int $value, ?Endian $endian = null): string
    {
        return pack('I*', $value & 0xFFFFFFFF, ($value >> 32) & 0xFFFFFFFF);

        return sprintf(
            '%c%c%c%c%c%c%c%c',
            ($value & 0xFF),
            (($value >> 8) & 0xFF),
            (($value >> 16) & 0xFF),
            (($value >> 24) & 0xFF),
            (($value >> 32) & 0xFF),
            (($value >> 40) & 0xFF),
            (($value >> 48) & 0xFF),
            (($value >> 56) & 0xFF)
        );

        return
            chr($value & 0xFF) .
            chr(($value >> 8) & 0xFF) .
            chr(($value >> 16) & 0xFF) .
            chr(($value >> 24) & 0xFF) .
            chr(($value >> 32) & 0xFF) .
            chr(($value >> 40) & 0xFF) .
            chr(($value >> 48) & 0xFF) .
            chr(($value >> 56) & 0xFF);
    }

    public static function getBinarySingle(float $value, ?Endian $endian = null): string
    {
        return self::getEndian($endian)->convert(pack('f', $value));
    }

    public static function getBinaryDouble(float $value, ?Endian $endian = null): string
    {
        return self::getEndian($endian)->convert(pack('d', $value));
    }

    public static function getBinaryBool(bool $value): string
    {
        return $value ? "\x01" : "\x00";
    }
}

// optimization to set the default endian early.
Reflection::getReflectionClass(BitConverter::class)->setStaticPropertyValue('defaultEndian', Endian::getDefault());
