<?php
namespace Orolyn\Primitive;

use Orolyn\Endian;

final class TypeDouble extends Primitive
{
    public function __construct($value, ?Endian $endian = null)
    {
        $this->endian = $endian ?? Endian::getDefault();

        if (is_string($value)) {
            parent::__construct(unpack('d', $this->endian->convert($value))[1]);
        } else {
            parent::__construct((float)$value);
        }
    }

    public function getBytes(): string
    {
        return $this->endian->convert(pack('d', $this->value));
    }
}
