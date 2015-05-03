<?php
namespace Orolyn;

use Orolyn\Primitive\TypeInt32;
use Orolyn\Primitive\TypeInt64;
use function Orolyn\Lang\Int32;
use function Orolyn\Lang\String;

final class Random
{
    /**
     * Random constructor.
     */
    private function __construct()
    {
    }

    /**
     * @param int $a
     * @param int $b
     * @return int
     */
    public static function generate(int $a = 0, int $b = TypeInt32::MAX_VALUE): int
    {
        return mt_rand($a, $b);
    }

    /**
     * Generate a string of bytes of specified length and random value.
     *
     * @param int $length
     * @return string
     */
    public static function generateBytes(int $length): string
    {
        $bytes = '';

        for ($i = 0; $i < $length; $i += 4) {
            $bytes .= Int32(self::generate())->getBytes();
        }

        return String($bytes)->substring(0, $length);
    }

    /**
     * Generate a random double between 0.0 and 1.0.
     *
     * @return float
     */
    public static function generateDouble(): float
    {
        return mt_rand(0, TypeInt64::MAX_VALUE) / TypeInt64::MAX_VALUE;
    }
}
