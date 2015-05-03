<?php
namespace Orolyn\Primitive;

use Orolyn\Endian;
use Orolyn\Serialization\ISerializable;
use Orolyn\Serialization\SerializationInfo;

abstract class Primitive implements ISerializable
{
    protected $value;
    protected ?Endian $endian = null;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function equals($value): bool
    {
        if ($value instanceof Primitive && $this->value === $value->value) {
            return true;
        }

        return $this->value === $value;
    }

    public function getHashCode(): int
    {
        return EqualityComparer::getDefault()->generateHashCode($this->value);
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }

    public function getValue()
    {
        return $this->value;
    }

    abstract function getBytes(): string;

    public function getObjectData(SerializationInfo $info): void
    {
        $info->replaceWith($this->value);
    }

    public function setObjectData(SerializationInfo $info): void
    {
    }
}
