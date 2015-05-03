<?php
namespace Orolyn\Primitive;

use Orolyn\Endian;

final class TypeUnsignedInt8 extends Primitive
{
    public const MAX_VALUE = 255;

    public function __construct($value)
    {
        if ($value instanceof Primitive) {
            $value = $value->value;
        }

        if (is_string($value)) {
            $value = ord($value[0]);
        }

        parent::__construct((int)$value & 0xFF);
    }

    public function getBytes(): string
    {
        return chr($this->value);
    }
}
