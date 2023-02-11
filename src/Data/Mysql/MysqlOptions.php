<?php

namespace Orolyn\Data\Mysql;

use Orolyn\Data\Mysql\Protocol\CharacterSet;
use Orolyn\Data\Mysql\Protocol\MysqlCapabilityList;
use Orolyn\Version;

/**
 * @internal
 */
class MysqlOptions
{
    public ?CharacterSet $characterSet = null;
    public int $maxPacketSize = (1024 ** 2) * 16; //16mb
    public ?string $username = null;
    public ?string $password = null;
    public ?string $database = null;
    public ?Version $serverVersion;

    public function __construct(array $options)
    {
    }
}
