<?php

namespace Orolyn\Net\Security\TLS\Structure;

enum VariableLength
{
    case UInt8;
    case UInt16;
    case UInt24;
    case UInt32;
}
