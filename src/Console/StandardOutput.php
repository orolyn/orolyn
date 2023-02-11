<?php

namespace Orolyn\Console;

use Orolyn\IO\EndianTrait;
use Orolyn\IO\IOutputStream;
use Orolyn\IO\OutputStreamTrait;

class StandardOutput implements IOutputStream
{
    use EndianTrait;
    use OutputStreamTrait;

    public function write(string $bytes): void
    {
        echo $bytes;
    }
}
