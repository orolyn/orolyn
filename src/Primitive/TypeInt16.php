<?php
namespace Orolyn\Primitive;

use Orolyn\Endian;

final class TypeInt16 extends Primitive
{
    public const MAX_VALUE = 32767;
    public const MIN_VALUE = -(self::MAX_VALUE)-1;

    public function __construct($value, ?Endian $endian = null)
    {
        $this->endian = $endian ?? Endian::getDefault();

        if ($value instanceof Primitive) {
            $value = $value->value;
        }

        if (is_string($value)) {
            $value = (int)unpack('s', $this->endian->convert($value))[1];
        }

        parent::__construct(((int)$value & 0xFFFF) | ((((int)$value & 0xFFFF) >> 15) * (((2 ** 48) - 1) << 16)));
    }

    public function getBytes(): string
    {
        return $this->endian->convert(pack('s', $this->value));
    }
}
