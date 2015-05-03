<?php
namespace Orolyn\Primitive;

use Orolyn\Endian;

final class TypeUnsignedInt16 extends Primitive
{
    public const MAX_VALUE = 65535;

    public function __construct($value, ?Endian $endian = null)
    {
        $this->endian = $endian ?? Endian::getDefault();

        if ($value instanceof Primitive) {
            $value = $value->value;
        }

        if (is_string($value)) {
            $value = (int)unpack('S', $this->endian->convert($value))[1];
        }

        parent::__construct((((int)$value) & 0xFFFF));
    }

    public function getBytes(): string
    {
        return $this->endian->convert(pack('S', $this->value));
    }
}
