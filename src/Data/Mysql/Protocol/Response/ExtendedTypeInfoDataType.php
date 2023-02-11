<?php

namespace Orolyn\Data\Mysql\Protocol\Response;

use Orolyn\IO\ByteStream;

enum ExtendedTypeInfoDataType: int
{
    case Type = 0x00;
    case Format = 0x01;
}
