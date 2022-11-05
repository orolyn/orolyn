<?php

namespace Orolyn\Serialization;

use Closure;
use Orolyn\IO\IInputStream;

/**
 * @template ContextType is SerializationContext
 */
class DecodingInfo
{
    /**
     * @param ContextType $context
     * @param Closure $decode
     */
    public function __construct(
        public readonly SerializationContext $context,
        private Closure $decode
    ) {
    }

    public function getOpaque(int $length): string
    {
        return $this->decode->__invoke(SerializationDataType::Opaque, $length);
    }

    public function getUnsignedInt8(): int
    {
        return $this->decode->__invoke(SerializationDataType::UnsignedInt8);
    }

    public function getUnsignedInt16(): int
    {
        return $this->decode->__invoke(SerializationDataType::UnsignedInt16);
    }

    public function getUnsignedInt24(): int
    {
        return $this->decode->__invoke(SerializationDataType::UnsignedInt24);
    }

    public function getUnsignedInt32(): int
    {
        return $this->decode->__invoke(SerializationDataType::UnsignedInt32);
    }

    public function getUnsignedInt64(): int
    {
        return $this->decode->__invoke(SerializationDataType::UnsignedInt64);
    }

    public function getInt8(): int
    {
        return $this->decode->__invoke(SerializationDataType::Int8);
    }

    public function getInt16(): int
    {
        return $this->decode->__invoke(SerializationDataType::Int16);
    }

    public function getInt24(): int
    {
        return $this->decode->__invoke(SerializationDataType::Int24);
    }

    public function getInt32(): int
    {
        return $this->decode->__invoke(SerializationDataType::Int32);
    }

    public function getInt64(): int
    {
        return $this->decode->__invoke(SerializationDataType::Int64);
    }

    public function getSingle(): float
    {
        return $this->decode->__invoke(SerializationDataType::Single);
    }

    public function getDouble(): float
    {
        return $this->decode->__invoke(SerializationDataType::Double);
    }

    public function getBool(): bool
    {
        return $this->decode->__invoke(SerializationDataType::Bool);
    }

    /**
     * @template T is IEncodable
     * @param class-string<T> $className
     * @return T
     */
    public function getObject(string $className): IEncodable
    {
        return $this->decode->__invoke(SerializationDataType::Object, $className);
    }
}
