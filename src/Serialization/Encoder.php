<?php

namespace Orolyn\Serialization;

use Closure;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;
use Orolyn\Reflection;
use RuntimeException;

abstract class Encoder implements IEncoder
{
    public function __construct(
        public SerializationContext $context = new SerializationContext()
    ) {
    }

    /**
     * @inheritDoc
     */
    public function decode(IInputStream $stream, string $className): IEncodable
    {
        $reflectionClass = Reflection::getReflectionClass($className);

        if (!in_array(IEncodable::class, $reflectionClass->getInterfaceNames())) {
            throw new RuntimeException();
        }

        /** @var IEncodable $instance */
        $instance = $reflectionClass->newInstanceWithoutConstructor();
        $instance->setDecodedObjectData(
            new DecodingInfo(
                $this->context,
                function (SerializationDataType $type, mixed $extra = null) use ($stream) {
                    return match ($type) {
                        SerializationDataType::Opaque => $this->decodeOpaque($stream, $extra),
                        SerializationDataType::UnsignedInt8  => $this->decodeUnsignedInt8($stream),
                        SerializationDataType::UnsignedInt16 => $this->decodeUnsignedInt16($stream),
                        SerializationDataType::UnsignedInt24 => $this->decodeUnsignedInt24($stream),
                        SerializationDataType::UnsignedInt32 => $this->decodeUnsignedInt32($stream),
                        SerializationDataType::UnsignedInt64 => $this->decodeUnsignedInt64($stream),
                        SerializationDataType::Int8  => $this->decodeInt8($stream),
                        SerializationDataType::Int16 => $this->decodeInt16($stream),
                        SerializationDataType::Int24 => $this->decodeInt24($stream),
                        SerializationDataType::Int32 => $this->decodeInt32($stream),
                        SerializationDataType::Int64 => $this->decodeInt64($stream),
                        SerializationDataType::Single => $this->decodeSingle($stream),
                        SerializationDataType::Double => $this->decodeDouble($stream),
                        SerializationDataType::Bool => $this->decodeBool($stream),
                        SerializationDataType::Object => $this->decode($stream, $extra),
                    };
                }
            )
        );

        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function encode(IOutputStream $stream, IEncodable $object): void
    {
        $object->getEncodedObjectData(
            new EncodingInfo(
                $this->context,
                function (SerializationDataType $type, mixed $value) use ($stream) {
                    match ($type) {
                        SerializationDataType::Opaque => $this->encodeOpaque($stream, $value),
                        SerializationDataType::UnsignedInt8  => $this->encodeUnsignedInt8($stream, $value),
                        SerializationDataType::UnsignedInt16 => $this->encodeUnsignedInt16($stream, $value),
                        SerializationDataType::UnsignedInt24 => $this->encodeUnsignedInt24($stream, $value),
                        SerializationDataType::UnsignedInt32 => $this->encodeUnsignedInt32($stream, $value),
                        SerializationDataType::UnsignedInt64 => $this->encodeUnsignedInt64($stream, $value),
                        SerializationDataType::Int8  => $this->encodeInt8($stream, $value),
                        SerializationDataType::Int16 => $this->encodeInt16($stream, $value),
                        SerializationDataType::Int24 => $this->encodeInt24($stream, $value),
                        SerializationDataType::Int32 => $this->encodeInt32($stream, $value),
                        SerializationDataType::Int64 => $this->encodeInt64($stream, $value),
                        SerializationDataType::Single => $this->encodeSingle($stream, $value),
                        SerializationDataType::Double => $this->encodeDouble($stream, $value),
                        SerializationDataType::Bool => $this->encodeBool($stream, $value),
                        SerializationDataType::Object => $this->encode($stream, $value),
                    };
                }
            )
        );
    }

    abstract protected function decodeOpaque(IInputStream $stream, int $length): string;
    abstract protected function decodeUnsignedInt8(IInputStream $stream): int;
    abstract protected function decodeUnsignedInt16(IInputStream $stream): int;
    abstract protected function decodeUnsignedInt24(IInputStream $stream): int;
    abstract protected function decodeUnsignedInt32(IInputStream $stream): int;
    abstract protected function decodeUnsignedInt64(IInputStream $stream): int;
    abstract protected function decodeInt8(IInputStream $stream): int;
    abstract protected function decodeInt16(IInputStream $stream): int;
    abstract protected function decodeInt24(IInputStream $stream): int;
    abstract protected function decodeInt32(IInputStream $stream): int;
    abstract protected function decodeInt64(IInputStream $stream): int;
    abstract protected function decodeSingle(IInputStream $stream): float;
    abstract protected function decodeDouble(IInputStream $stream): float;
    abstract protected function decodeBool(IInputStream $stream): bool;

    abstract protected function encodeOpaque(IOutputStream $stream, string $value): void;
    abstract protected function encodeUnsignedInt8(IOutputStream $stream, int $value): void;
    abstract protected function encodeUnsignedInt16(IOutputStream $stream, int $value): void;
    abstract protected function encodeUnsignedInt24(IOutputStream $stream, int $value): void;
    abstract protected function encodeUnsignedInt32(IOutputStream $stream, int $value): void;
    abstract protected function encodeUnsignedInt64(IOutputStream $stream, int $value): void;
    abstract protected function encodeInt8(IOutputStream $stream, int $value): void;
    abstract protected function encodeInt16(IOutputStream $stream, int $value): void;
    abstract protected function encodeInt24(IOutputStream $stream, int $value): void;
    abstract protected function encodeInt32(IOutputStream $stream, int $value): void;
    abstract protected function encodeInt64(IOutputStream $stream, int $value): void;
    abstract protected function encodeSingle(IOutputStream $stream, float $value): void;
    abstract protected function encodeDouble(IOutputStream $stream, float $value): void;
    abstract protected function encodeBool(IOutputStream $stream, bool $value): void;
}
