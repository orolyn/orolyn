<?php

namespace Orolyn\Data\Mysql\Protocol\Command\Text;

use Orolyn\Data\Mysql\Protocol\Command\Command;
use Orolyn\Data\Mysql\Protocol\Packet;
use Orolyn\IO\ByteStream;

class Quit extends Command
{
    public function getHeader(): int
    {
        return Command::QUIT;
    }
}
