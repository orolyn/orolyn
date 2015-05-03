<?php
namespace Orolyn;

use Orolyn\IO\FileAccess;
use Orolyn\IO\FileMode;
use Orolyn\IO\FileStream;
use Orolyn\IO\FileStreamOptions;
use Orolyn\IO\IInputStream;
use Orolyn\Primitive\TypeInt32;
use Orolyn\Primitive\TypeInt64;
use function Orolyn\Lang\Int32;
use function Orolyn\Lang\String;

final class SecureRandom
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
        return 0; // TODO
    }

    /**
     * Generate a string of bytes of specified length and random value.
     *
     * @param int $length
     * @return string
     */
    public static function generateBytes(int $length): string
    {
        return self::getStream()->read($length);
    }

    /**
     * Generate a random double between 0.0 and 1.0.
     *
     * @return float
     */
    public static function generateDouble(): float
    {
        return (self::getStream()->readInt64() & TypeInt64::MAX_VALUE) / TypeInt64::MAX_VALUE;
    }

    private static function getStream(): IInputStream
    {
        static $stream;

        if (null === $stream) {
            $stream = new FileStream('/dev/random', new FileStreamOptions(FileMode::Open, FileAccess::Read, false));
        }

        return $stream;
    }
}
