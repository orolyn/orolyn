<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\Net\Security\TLS\Context;
enum VariableLength
{
    case UInt8;
    case UInt16;
    case UInt24;
    case UInt32;
}
