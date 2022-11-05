<?php
namespace Orolyn;

use ReflectionEnum;

enum Endian
{
    case BigEndian;
    case LittleEndian;
    case Default;

    public static function getDefault(): Endian
    {
        static $default;

        if (null === $default) {
            $int = 0x00FF;
            $p = pack('S', $int);

            $isBig = $int !== current(unpack('v', $p));

            $default = $isBig ? self::BigEndian : self::LittleEndian;
        }

        return $default;
    }

    /**
     * Converts if this endian does not equal the default endian, then the input bytes are reversed.
     *
     * @param string $bytes
     * @return string
     */
    public function convert(string $bytes): string
    {
        if ($this !== self::getDefault()) {
            return strrev($bytes);
        }

        return $bytes;
    }
}
