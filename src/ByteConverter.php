<?php

namespace Orolyn;

class ByteConverter
{
    private const MAX_INT_24 = (2**23)-1;

    public static function getInt8(string|int $value): int
    {
        if (is_int($value)) {
            return ($value & 0xFF) | ((($value & 0xFF) >> 7) * (((2 ** 56) - 1) << 8));
        }

        return (int)unpack('c', $value)[1];
    }

    public static function getInt16(string|int $value, ?Endian $endian = null): int
    {
        if (is_int($value)) {
            return ($value & 0xFFFF) | ((($value & 0xFFFF) >> 15) * (((2 ** 48) - 1) << 16));
        }

        return (int)unpack('s', self::getEndian($endian)->convert($value))[1];;
    }

    public static function getInt24(string|int $value, ?Endian $endian = null): int
    {
        if (is_int($value)) {
            return ($value & 0xFFFFFF) | ((($value & 0xFFFFFF) >> 23) * (((2 ** 40) - 1) << 24));
        }

        if (self::getEndian($endian) === Endian::BigEndian) {
            $int = (ord($value[0]) << 16) |  (ord($value[1]) << 8) | ord($value[2]);
        } else {
            $int = (ord($value[2]) << 16) |  (ord($value[1]) << 8) | ord($value[0]);
        }

        if ($int <= self::MAX_INT_24) {
            return $int;
        }

        return (-self::MAX_INT_24 - 2) + ($int - self::MAX_INT_24);
    }

    public static function getInt32(string|int $value, ?Endian $endian = null): int
    {
        if (is_int($value)) {
            return ($value & 0xFFFFFFFF) | ((($value & 0xFFFFFFFF) >> 31) * (((2 ** 32) - 1) << 32));
        }

        return (int)unpack('l', self::getEndian($endian)->convert($value))[1];
    }

    public static function getInt64(string|int $value, ?Endian $endian = null): int
    {
        if (is_int($value)) {
            return $value;
        }

        return (int)unpack('q', self::getEndian($endian)->convert($value))[1];
    }

    public static function getUnsignedInt8(string|int $value): int
    {
        if (is_int($value)) {
            return $value & 0xFF;
        }

        return ord($value[0]);
    }

    public static function getUnsignedInt16(string|int $value, ?Endian $endian = null): int
    {
        if (is_int($value)) {
            return (($value) & 0xFFFF);
        }

        return (int)unpack('S', self::getEndian($endian)->convert($value))[1];
    }

    public static function getUnsignedInt24(string|int $value, ?Endian $endian = null): int
    {
        if (is_int($value)) {
            return (($value) & 0xFFFFFF);
        }

        if (self::getEndian($endian) === Endian::BigEndian) {
            return (ord($value[0]) << 16) |  (ord($value[1]) << 8) | ord($value[2]);
        } else {
            return (ord($value[2]) << 16) |  (ord($value[1]) << 8) | ord($value[0]);
        }
    }

    public static function getUnsignedInt32(string|int $value, ?Endian $endian = null): int
    {
        if (is_int($value)) {
            return ($value) & 0xFFFFFFFF;
        }

        return (int)unpack('L', self::getEndian($endian)->convert($value))[1];
    }

    public static function getUnsignedInt64(string|int $value, ?Endian $endian = null): int
    {
        if (is_int($value)) {
            return ($value) & 0xFFFFFFFFFFFFFFFF;
        }

        return (int)unpack('Q', self::getEndian($endian)->convert($value))[1];
    }

    public static function getFloat(string|float $value, ?Endian $endian = null): float
    {
        if (is_float($value)) {
            return round($value, 7);
        }

        return round(unpack('f', self::getEndian($endian)->convert($value))[1], 7);
    }

    public static function getDouble(string|float $value, ?Endian $endian = null): float
    {
        if (is_float($value)) {
            return $value;
        }

        return (float)unpack('Q', self::getEndian($endian)->convert($value))[1];
    }

    public static function getBool(string|bool $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

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
        if (self::getEndian($endian) === Endian::BigEndian) {
            return chr(($value >> 16) & 0xFF) . chr(($value >> 8) & 0xFF) . chr($value & 0xFF);
        } else {
            return chr($value & 0xFF) . chr(($value >> 8) & 0xFF) . chr(($value >> 16) & 0xFF);
        }
    }

    public static function getBinaryUnsignedInt32(int $value, ?Endian $endian = null): string
    {
        return self::getEndian($endian)->convert(pack('L', $value));
    }

    public static function getBinaryUnsignedInt64(int $value, ?Endian $endian = null): string
    {
        return self::getEndian($endian)->convert(pack('Q', $value));
    }

    public static function getBinaryFloat(float $value, ?Endian $endian = null): string
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

    private static function getEndian(?Endian $endian): Endian
    {
        return $endian ?? Endian::getDefault();
    }
}
