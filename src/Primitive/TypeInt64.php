<?php
namespace Orolyn\Primitive;

use Orolyn\Endian;

/**
 * Intended for binary formatting. There is no unsigned equivalent.
 *
 * Class TypeInt64
 * @package Orolyn\Primitive
 */
final class TypeInt64 extends Primitive
{
    public const MAX_VALUE = PHP_INT_MAX;
    public const MIN_VALUE = -(self::MAX_VALUE)-1;

    public function __construct($value, ?Endian $endian = null)
    {
        $this->endian = $endian ?? Endian::getDefault();

        if (is_string($value)) {
            $value = (int)unpack('q', $this->endian->convert($value))[1];
        }

        parent::__construct((int)$value);
    }

    public function getBytes(): string
    {
        return $this->endian->convert(pack('q', $this->value));
    }
}
