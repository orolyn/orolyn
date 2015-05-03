<?php
namespace Orolyn\Primitive;

use Orolyn\Endian;

final class TypeUnsignedInt64 extends Primitive
{
    public const MAX_VALUE = 4294967295;

    public function __construct($value, ?Endian $endian = null)
    {
        $this->endian = $endian ?? Endian::getDefault();

        if ($value instanceof Primitive) {
            $value = $value->value;
        }

        if (is_string($value)) {
            $value = (int)unpack('Q', $this->endian->convert($value))[1];
        }

        parent::__construct(((int)$value) & 0xFFFFFFFF);
    }

    public function getBytes(): string
    {
        return $this->endian->convert(pack('Q', $this->value));
    }
}
