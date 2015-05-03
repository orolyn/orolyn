<?php
namespace Orolyn\Security\Cryptopgraphy;

use Orolyn\Concurrency\Coroutine;
use Orolyn\IO\File;
use Orolyn\IO\FileMode;
use Orolyn\IO\FileStream;
use Orolyn\Math;

class RandomNumberGenerator
{
    public static function create(): RandomNumberGenerator
    {
        static $instance;

        return $instance ?? $instance = new RandomNumberGenerator();
    }

    public function getBytes(int $length): string
    {
        return random_bytes($length);
    }
}
