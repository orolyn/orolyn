<?php

namespace Orolyn\Serialization;

use ArrayIterator;
use Closure;
use IteratorAggregate;
use Orolyn\IO\IOutputStream;
use Traversable;

/**
 * @template ContextType is SerializationContext
 */
class EncodingInfo
{
    /**
     * @param ContextType $context
     * @param Closure $encode
     */
    public function __construct(
        public readonly SerializationContext $context,
        private Closure $encode
    ) {
    }

    public function addOpaque(string $value): void
    {
        $this->encode->__invoke(SerializationDataType::Opaque, $value);
    }

    public function addUnsignedInt8(int $value): void
    {
        $this->encode->__invoke(SerializationDataType::UnsignedInt8, $value);
    }

    public function addUnsignedInt16(int $value): void
    {
        $this->encode->__invoke(SerializationDataType::UnsignedInt16, $value);
    }

    public function addUnsignedInt24(int $value): void
    {
        $this->encode->__invoke(SerializationDataType::UnsignedInt24, $value);
    }

    public function addUnsignedInt32(int $value): void
    {
        $this->encode->__invoke(SerializationDataType::UnsignedInt32, $value);
    }

    public function addUnsignedInt64(int $value): void
    {
        $this->encode->__invoke(SerializationDataType::UnsignedInt64, $value);
    }

    public function addInt8(int $value): void
    {
        $this->encode->__invoke(SerializationDataType::Int8, $value);
    }

    public function addInt16(int $value): void
    {
        $this->encode->__invoke(SerializationDataType::Int16, $value);
    }

    public function addInt24(int $value): void
    {
        $this->encode->__invoke(SerializationDataType::Int24, $value);
    }

    public function addInt32(int $value): void
    {
        $this->encode->__invoke(SerializationDataType::Int32, $value);
    }

    public function addInt64(int $value): void
    {
        $this->encode->__invoke(SerializationDataType::Int64, $value);
    }

    public function addSingle(float $value): void
    {
        $this->encode->__invoke(SerializationDataType::Single, $value);
    }

    public function addDouble(float $value): void
    {
        $this->encode->__invoke(SerializationDataType::Double, $value);
    }

    public function addBool(bool $value): void
    {
        $this->encode->__invoke(SerializationDataType::Bool, $value);
    }

    public function addObject(IEncodable $object): void
    {
        $this->encode->__invoke(SerializationDataType::Object, $object);
    }
}
