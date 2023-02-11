<?php

namespace Orolyn\Data\Mysql\Protocol\Command\Text;

use Orolyn\Data\Mysql\Protocol\Command\Command;
use Orolyn\Data\Mysql\Protocol\Packet;
use Orolyn\IO\ByteStream;

class Query extends Command
{
    public function __construct(
        private string $statement
    ) {
    }

    public function getPayload(ByteStream $stream): void
    {
        $stream->write($this->statement);
    }

    public function getHeader(): int
    {
        return Command::QUERY;
    }
}
