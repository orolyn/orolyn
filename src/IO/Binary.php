<?php

namespace Orolyn\IO;

class Binary
{
    /**
     * @param string $bytes
     * @return int
     */
    public static function getLength(string $bytes): int
    {
        return mb_strlen($bytes, '8bit');
    }

    /**
     * @param string $bytes
     * @param int $start
     * @param int|null $length
     * @return string
     */
    public static function getSubstring(string $bytes, int $start, ?int $length = null): string
    {
        return mb_substr($bytes, $start, $length, '8bit');
    }

    public static function truncate(string $bytes, int $length): string
    {
        return self::getSubstring($bytes, 0, $length);
    }
}
