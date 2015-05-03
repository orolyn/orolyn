<?php
namespace Orolyn\Primitive;

use Orolyn\Endian;

final class TypeFloat extends Primitive
{
    public function __construct($value, ?Endian $endian = null)
    {
        $this->endian = $endian ?? Endian::getDefault();

        if (!is_string($value)) {
            $value = pack('f', (float)$value);
        }

        parent::__construct(round(unpack('f', $this->endian->convert($value))[1], 7));
    }

    public function getBytes(): string
    {
        return $this->endian->convert(pack('f', $this->value));
    }
}
