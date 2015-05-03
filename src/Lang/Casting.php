<?php
namespace Orolyn\Lang;

use Orolyn\ArgumentException;
use Orolyn\Endian;
use Orolyn\Primitive\Primitive;
use Orolyn\Primitive\TypeBool;
use Orolyn\Primitive\TypeDouble;
use Orolyn\Primitive\TypeFloat;
use Orolyn\Primitive\TypeInt16;
use Orolyn\Primitive\TypeInt32;
use Orolyn\Primitive\TypeInt64;
use Orolyn\Primitive\TypeInt8;
use Orolyn\Primitive\TypeString;
use Orolyn\Primitive\TypeUnsignedInt16;
use Orolyn\Primitive\TypeUnsignedInt32;
use Orolyn\Primitive\TypeUnsignedInt64;
use Orolyn\Primitive\TypeUnsignedInt8;

function Native($source): array
{
    if (is_array($source) || is_scalar($source)) {
        return $source;
    }

    if (is_iterable($source)) {
        $return = [];

        foreach ($source as $key => $value) {
            $return[$key] = $value;
        }

        return $return;
    }

    if ($source instanceof Primitive) {
        return $source->getValue();
    }
}

define('TYPE_OF_NATIVE', 1);
define('TYPE_OF_OBJECT', 2);

function TypeOf($value, int $flags = 0): ?string
{
    switch (gettype($value)) {
        case 'boolean' : return 'bool';
        case 'integer' : return 'int';
        case 'double'  : return 'float';
        case 'float'   : return 'float';
        case 'string'  : return 'string';
        case 'array'   : return 'array';
        case 'resource': return 'pointer';
        case 'NULL'    : return 'null';
        case 'object':
            if ($flags & TYPE_OF_NATIVE && $value instanceof Primitive) {
                return TypeOf($value->getValue());
            }

            if ($flags & TYPE_OF_OBJECT) {
                return get_class($value);
            }

            return 'object';
    }

    if (is_callable($value)) {
        return 'closure';
    }

    return null;
}

function VarDumpBinary(string $string): void
{
    for ($i = 0; $i < strlen($string); $i++) {
        var_dump(str_pad(decbin(hexdec(bin2hex($string[$i]))), 8, '0', STR_PAD_LEFT));
    }
}

function Int8($value): TypeInt8
{
    return new TypeInt8($value);
}

function Int16($value, ?Endian $endian = null): TypeInt16
{
    return new TypeInt16($value, $endian);
}

function Int32($value, ?Endian $endian = null): TypeInt32
{
    return new TypeInt32($value, $endian);
}

function Int64($value, ?Endian $endian = null): TypeInt64
{
    return new TypeInt64($value, $endian);
}

function UnsignedInt8($value): TypeUnsignedInt8
{
    return new TypeUnsignedInt8($value);
}

function UnsignedInt16($value, ?Endian $endian = null): TypeUnsignedInt16
{
    return new TypeUnsignedInt16($value, $endian);
}

function UnsignedInt32($value, ?Endian $endian = null): TypeUnsignedInt32
{
    return new TypeUnsignedInt32($value, $endian);
}

function UnsignedInt64($value, ?Endian $endian = null): TypeUnsignedInt64
{
    return new TypeUnsignedInt64($value, $endian);
}

function Float($value, ?Endian $endian = null): TypeFloat
{
    return new TypeFloat($value, $endian);
}

function Double($value, ?Endian $endian = null): TypeDouble
{
    return new TypeDouble($value, $endian);
}

function Bool($value): TypeBool
{
    return new TypeBool($value);
}

function String($value = ''): TypeString
{
    return new TypeString($value);
}

function Length($value): int
{
    if (is_array($value) || $value instanceof \Countable) {
        return count($value);
    }

    if (is_string($value)) {
        return strlen($value);
    }

    throw new ArgumentException();
}
