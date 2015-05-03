<?php
namespace Orolyn\Primitive;

use Orolyn\Endian;

final class TypeInt8 extends Primitive
{
    public const MAX_VALUE = 127;
    public const MIN_VALUE = -128;

    public function __construct($value)
    {
        if ($value instanceof Primitive) {
            $value = $value->value;
        }

        if (is_string($value)) {
            $value = (int)unpack('c', $value)[1];
        }

        parent::__construct(((int)$value & 0xFF) | ((((int)$value & 0xFF) >> 7) * (((2 ** 56) - 1) << 8)));
    }

    public function getBytes(): string
    {
        return pack('c', $this->value);
    }
}
