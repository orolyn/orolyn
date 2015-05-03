<?php
namespace Orolyn\Primitive;

final class TypeBool extends Primitive
{
    public function __construct($value)
    {
        if ($value instanceof Primitive) {
            $value = $value->value;
        }

        parent::__construct((bool)$value);
    }

    public function getBytes(): string
    {
        return (int)$this->value;
    }
}
