<?php

namespace Orolyn\Serialization;

enum SerializationDataType
{
    case Opaque;
    case UnsignedInt8;
    case UnsignedInt16;
    case UnsignedInt24;
    case UnsignedInt32;
    case UnsignedInt64;
    case Int8;
    case Int16;
    case Int24;
    case Int32;
    case Int64;
    case Single;
    case Double;
    case Bool;
    case Object;
}
