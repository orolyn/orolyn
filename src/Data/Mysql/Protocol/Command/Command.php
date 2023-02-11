<?php

namespace Orolyn\Data\Mysql\Protocol\Command;

use Orolyn\IO\ByteStream;

abstract class Command
{
    public const QUIT = 1;

    public const QUERY = 3;

    public function getPayload(ByteStream $stream): void
    {
    }

    abstract public function getHeader(): int;
}
