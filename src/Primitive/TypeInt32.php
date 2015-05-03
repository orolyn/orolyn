<?php
namespace Orolyn\Primitive;

use Orolyn\Endian;

final class TypeInt32 extends Primitive
{
    public const MAX_VALUE = 2147483647;
    public const MIN_VALUE = -(self::MAX_VALUE)-1;

    public function __construct($value, ?Endian $endian = null)
    {
        $this->endian = $endian ?? Endian::getDefault();

        if ($value instanceof Primitive) {
            $value = $value->value;
        }

        if (is_string($value)) {
            $value = (int)unpack('l', $this->endian->convert($value))[1];
        }

        parent::__construct(((int)$value & 0xFFFFFFFF) | ((((int)$value & 0xFFFFFFFF) >> 31) * (((2 ** 32) - 1) << 32)));
    }

    public function getBytes(): string
    {
        return $this->endian->convert(pack('l', $this->value));
    }
}
