<?php
namespace Orolyn\Serialization;

use Orolyn\Collection\Dictionary;
use Orolyn\Collection\IList;
use Orolyn\Collection\KeyValuePair;
use Orolyn\Collection\Queue;
use Orolyn\Delegate;
use Orolyn\Event\DeserializeEvent;
use Orolyn\Event\EventDispatcher;
use Orolyn\Event\EventLoop;
use Orolyn\Event\SerializeEvent;
use Orolyn\Exception;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;
use Orolyn\Math;
use function Orolyn\Lang\String;
use Orolyn\Primitive\TypeInt16;
use Orolyn\Primitive\TypeInt32;
use Orolyn\Primitive\TypeInt64;
use Orolyn\Primitive\TypeInt8;
use Orolyn\Serialization\Extension\NativeDateTimeExtension;
use Orolyn\Serialization\Extension\NativeStdClassExtension;

class BinaryFormatter extends EventDispatcher
{
    private const TYPE_INT    = 1; // 0000 0001
    private const TYPE_FLOAT  = 2; // 0000 0010
    private const TYPE_STRING = 3; // 0000 0011
    private const TYPE_ARRAY  = 4; // 0000 0100
    private const TYPE_OBJECT = 5; // 0000 0101
    private const TYPE_NULL   = 6; // 0000 0110
    private const TYPE_TRUE   = 7; // 0000 0111
    private const TYPE_FALSE  = 8; // 0000 1000

    /**
     * @var Dictionary
     */
    private static $supportedTypes;

    /**
     * @var Queue
     */
    private $serializeQueue;

    /**
     * @var \Generator
     */
    private $serializeCoroutine;

    /**
     * @var Queue
     */
    private $deserializeQueue;

    /**
     * @var \Generator
     */
    private $deserializeCoroutine;

    /**
     * @var object
     */
    private $serializeJob;

    /**
     * @var object
     */
    private $deserializeJob;

    /**
     * @var Dictionary|IExtension[]
     */
    private $extensions;

    /**
     * BinaryFormatter constructor.
     * @param IList|IExtension[] $extensions
     */
    final public function __construct(IList $extensions = null, IList $classAliases)
    {
        $e1 = new NativeDateTimeExtension();
        $e2 = new NativeStdClassExtension();

        $this->extensions = new Dictionary(
            new KeyValuePair($e1->getType(), $e1),
            new KeyValuePair($e2->getType(), $e2)
        );

        if (null !== $extensions) {
            foreach ($extensions as $extension) {
                $this->extensions->add($extension->getType(), $extension);
            }
        }
    }

    public function serialize(IOutputStream $stream, $value): void
    {
        if (null === $this->serializeQueue) {
            $this->serializeQueue = new Queue();
        }

        $job = new class () {
            public $stream;
            public $value;
            public $references;
        };
        $job->stream = $stream;
        $job->value = $value;
        $job->references = new Dictionary();

        $this->serializeQueue->enqueue($job);
    }

    public function deserialize(IInputStream $stream): void
    {
        if (null === $this->deserializeQueue) {
            $this->deserializeQueue = new Queue();
        }

        $job = new class () {
            public $stream;
            public $value;
            public $references;
        };
        $job->stream = $stream;
        $job->references = new Dictionary();

        $this->deserializeQueue->enqueue($job);
    }

    protected function tick(EventLoop $eventLoop): void
    {
        if (null === $this->serializeCoroutine && $this->serializeQueue && $this->serializeQueue->getLength()) {
            $this->serializeJob = $this->serializeQueue->dequeue();
            $this->serializeCoroutine = $this->doSerialize(
                $this->serializeJob->stream,
                $this->serializeJob->value,
                $this->serializeJob->references,
                false
            );
        } elseif (null !== $this->serializeCoroutine) {
            $this->serializeCoroutine->next();
        }

        if (null === $this->deserializeCoroutine && $this->deserializeQueue && $this->deserializeQueue->getLength()) {
            $this->deserializeJob = $this->deserializeQueue->dequeue();
            $this->deserializeCoroutine = $this->doDeserialize(
                $this->deserializeJob->stream,
                $this->deserializeJob->value,
                $this->deserializeJob->references,
                false
            );
        } elseif (null !== $this->deserializeCoroutine) {
            $this->deserializeCoroutine->next();
        }

        if (null !== $this->serializeCoroutine && $this->serializeCoroutine->current()) {
            $job = $this->serializeJob;

            $this->serializeJob = null;
            $this->serializeCoroutine = null;

            $this->dispatchEvent(
                new SerializeEvent(
                    SerializeEvent::SERIALIZE,
                    false,
                    $job->stream,
                    $job->value
                )
            );
        }

        if ($this->deserializeCoroutine && $this->deserializeCoroutine->current()) {
            $job = $this->deserializeJob;

            $this->deserializeJob = null;
            $this->deserializeCoroutine = null;

            $this->dispatchEvent(
                new DeserializeEvent(
                    DeserializeEvent::DESERIALIZE,
                    false,
                    $job->stream,
                    $job->value
                )
            );
        }
    }

    public function isSupported($data, &$type = null): bool
    {
        if (null === self::$supportedTypes) {
            self::$supportedTypes = new Dictionary(
                new KeyValuePair('integer', self::TYPE_INT),
                new KeyValuePair('float', self::TYPE_FLOAT),
                new KeyValuePair('double', self::TYPE_FLOAT),
                new KeyValuePair('string', self::TYPE_STRING),
                new KeyValuePair('array', self::TYPE_ARRAY),
                new KeyValuePair('object', self::TYPE_OBJECT),
                new KeyValuePair('NULL', self::TYPE_NULL),
                new KeyValuePair('boolean', null)
            );
        }

        $typeString = gettype($data);

        if(self::$supportedTypes->try($typeString, $type)) {
            if ('boolean' === $typeString) {
                $type = $data ? self::TYPE_TRUE : self::TYPE_FALSE;
            }

            return true;
        }

        return false;
    }

    public function throwExceptionIfNotSupported($data, &$type = null): void
    {
        if (!self::isSupported($data, $type)) {
            throw new UnsupportedTypeException($type);
        }
    }

    private function waitPendingOutput(IOutputStream $stream): \Generator
    {
        while ($stream->getBytesPending() > 1024 * 8) {
            yield;
        }
    }

    private function waitAvailableInput(IInputStream $stream, int $length): \Generator
    {
        while ($stream->getBytesAvailable() < $length) {
            yield;
        }
    }

    private function doSerialize(IOutputStream $stream, $value, Dictionary $references, bool $nested = true): \Generator
    {
        $this->throwExceptionIfNotSupported($value, $type);

        switch ($type) {
            case self::TYPE_INT:
                yield from $this->doSerializeInt($stream, $value);
                break;
            case self::TYPE_FLOAT:
                yield from $this->doSerializeFloat($stream, $value);
                break;
            case self::TYPE_STRING:
                yield from $this->doSerializeString($stream, $value);
                break;
            case self::TYPE_ARRAY:
                yield from $this->doSerializeArray($stream, $value, $references);
                break;
            case self::TYPE_OBJECT:
                yield from $this->doSerializeObject($stream, $value, $references);
                break;
            case self::TYPE_NULL:
                yield from $this->doSerializeNull($stream);
                break;
            case self::TYPE_TRUE:
                yield from $this->doSerializeTrue($stream);
                break;
            case self::TYPE_FALSE:
                yield from $this->doSerializeFalse($stream);
                break;
        }

        if (false === $nested) {
            yield true;
        }
    }

    private function doSerializeInt(IOutputStream $stream, int $int): \Generator
    {
        yield from $this->waitPendingOutput($stream);

        switch (true) {
            case $int >= TypeInt8::MIN_VALUE && $int <= TypeInt8::MAX_VALUE:
                $stream->writeByte(self::TYPE_INT | (1 << 4));
                $stream->writeInt8($int);
                break;
            case $int >= TypeInt16::MIN_VALUE && $int <= TypeInt16::MAX_VALUE:
                $stream->writeByte(self::TYPE_INT | (1 << 5));
                $stream->writeInt16($int);
                break;
            case $int >= TypeInt32::MIN_VALUE && $int <= TypeInt32::MAX_VALUE:
                $stream->writeByte(self::TYPE_INT | (1 << 6));
                $stream->writeInt32($int);
                break;
            case $int >= TypeInt64::MIN_VALUE && $int <= TypeInt64::MAX_VALUE:
                $stream->writeByte(self::TYPE_INT | (1 << 7));
                $stream->writeInt64($int);
                break;
        }

        $stream->flush();
    }

    private function doSerializeFloat(IOutputStream $stream, float $float): \Generator
    {
        yield from $this->waitPendingOutput($stream);

        $stream->writeByte(self::TYPE_FLOAT);
        $stream->writeDouble($float);
        $stream->flush();
    }

    private function doSerializeString(IOutputStream $stream, string $string): \Generator
    {
        yield from $this->waitPendingOutput($stream);

        $stream->writeByte(self::TYPE_STRING);
        $this->writeString($stream, $string);
        $stream->flush();
    }

    private function doSerializeArray(IOutputStream $stream, array $array, Dictionary $references): \Generator
    {
        yield from $this->waitPendingOutput($stream);

        $stream->writeByte(self::TYPE_ARRAY);
        $this->write7BitEncodedInt($stream, count($array));

        foreach ($array as $key => $val) {
            yield from $this->doSerialize($stream, $key, $references);
            yield from $this->doSerialize($stream, $val, $references);
        }

        $stream->flush();
    }

    private function doSerializeObject(IOutputStream $stream, object $object, Dictionary $references): \Generator
    {
        yield from $this->waitPendingOutput($stream);

        if ($references->try($object, $ref)) {
            $stream->writeByte(self::TYPE_OBJECT | (1 << 4));
            $stream->writeInt32($ref);
        } else {
            /** @var IExtension $extension */
            if ($this->extensions->try(get_class($object), $extension)) {
                $this->writeObjectHeader($stream, $object, $references);

                yield from $extension->serialize(
                    $stream,
                    $object,
                    new Delegate(
                        null,
                        function ($value) use ($stream, $references) {
                            yield from $this->doSerialize($stream, $value, $references);
                        }
                    )
                );
            } elseif ($object instanceof ISerializable) {
                $info = new SerializationInfo();
                $object->getObjectData($info);

                if ($info->hasReplacement()) {
                    yield from $this->doSerialize($stream, $info->getReplacement(), $references);
                } else {
                    $this->writeObjectHeader($stream, $object, $references);

                    $this->write7BitEncodedInt($stream, $info->getLength());
                    foreach ($info as $key => $val) {
                        yield from $this->doSerialize($stream, $key, $references);
                        yield from $this->doSerialize($stream, $val, $references);
                    }
                }
            } else {
                $this->writeObjectHeader($stream, $object, $references);

                $reflectionClass = new \ReflectionClass($object);
                $properties = $reflectionClass->getProperties();

                $this->write7BitEncodedInt($stream, count($properties));

                foreach ($properties as $property) {
                    $property->setAccessible(true);

                    yield from $this->doSerialize($stream, $property->getName(), $references);
                    yield from $this->doSerialize($stream, $property->getValue($object), $references);
                }
            }
        }

        $stream->flush();
    }

    private function doSerializeNull(IOutputStream $stream): \Generator
    {
        yield from $this->waitPendingOutput($stream);

        $stream->writeByte(self::TYPE_NULL);
        $stream->flush();
    }

    private function doSerializeTrue(IOutputStream $stream): \Generator
    {
        yield from $this->waitPendingOutput($stream);

        $stream->writeByte(self::TYPE_TRUE);
        $stream->flush();
    }

    private function doSerializeFalse(IOutputStream $stream): \Generator
    {
        yield from $this->waitPendingOutput($stream);

        $stream->writeByte(self::TYPE_FALSE);
        $stream->flush();
    }

    private function writeObjectHeader(IOutputStream $stream, object $object, Dictionary $references): void
    {
        $class = get_class($object);

        if ($references->try($class, $ref)) {
            $stream->writeByte(self::TYPE_OBJECT | (1 << 5));
            $stream->writeInt32($ref);
        } else {
            $stream->writeByte(self::TYPE_OBJECT);
            $this->writeString($stream, get_class($object));

            $references->add($class, $references->getLength());
        }

        $references->add($object, $references->getLength());
    }

    private function writeString(IOutputStream $stream, string $value): void
    {
        $this->write7BitEncodedInt($stream, String($value)->getLength());
        $stream->write($value);
    }

    private function write7BitEncodedInt(IOutputStream $stream, int $value): void
    {
        do {
            $low = $value & 0x7F;
            $value >>= 7;
            if ($value > 0) {
                $low |= 0x80;
            }
            $stream->writeInt8($low);
        } while ($value > 0);
    }

    private function doDeserialize(IInputStream $stream, &$value, Dictionary $references, bool $nested = true): \Generator
    {
        yield from $this->waitAvailableInput($stream, 1);

        $type = $stream->readByte();

        switch ($type & 0xF) {
            case self::TYPE_INT:
                yield from $this->doDeserializeInt($stream, $value, $type);
                break;
            case self::TYPE_FLOAT:
                yield from $this->doDeserializeFloat($stream, $value);
                break;
            case self::TYPE_STRING:
                yield from $this->doDeserializeString($stream, $value);
                break;
            case self::TYPE_ARRAY:
                yield from $this->doDeserializeArray($stream, $value, $references);
                break;
            case self::TYPE_OBJECT:
                yield from $this->doDeserializeObject($stream, $value, $references, $type);
                break;
            case self::TYPE_NULL:
                $value = null;
                break;
            case self::TYPE_TRUE:
                $value = true;
                break;
            case self::TYPE_FALSE:
                $value = false;
                break;
        }

        if (false === $nested) {
            yield true;
        }
    }

    private function doDeserializeInt(IInputStream $stream, &$value, int $type): \Generator
    {
        switch (true) {
            case $type & (1 << 4):
                yield from $this->waitAvailableInput($stream, 1);
                $value = $stream->readInt8();
                break;
            case $type & (1 << 5):
                yield from $this->waitAvailableInput($stream, 2);
                $value = $stream->readInt16();
                break;
            case $type & (1 << 6):
                yield from $this->waitAvailableInput($stream, 4);
                $value = $stream->readInt32();
                break;
            case $type & (1 << 7):
                yield from $this->waitAvailableInput($stream, 8);
                $value = $stream->readInt64();
                break;
            default:
                throw new Exception();
        }
    }

    private function doDeserializeFloat(IInputStream $stream, &$value): \Generator
    {
        yield from $this->waitAvailableInput($stream, 8);

        $value = $stream->readDouble();
    }

    private function doDeserializeString(IInputStream $stream, &$value): \Generator
    {
        $size = yield from $this->decode7BitInt($stream);

        $value = '';

        while ($size > 0) {
            $read = Math::min($size, 1024 << 3);
            $size -= $read;

            yield from $this->waitAvailableInput($stream, $read);

            $value .= $stream->read($read);
        }
    }

    private function doDeserializeArray(IInputStream $stream, &$value, Dictionary $references): \Generator
    {
        $size = yield from $this->decode7BitInt($stream);

        $value = [];

        for ($i = 0; $i < $size; $i++) {
            yield from $this->doDeserialize($stream, $key, $references);
            yield from $this->doDeserialize($stream, $val, $references);

            $value[$key] = $val;
        }
    }

    private function doDeserializeObject(IInputStream $stream, &$value, Dictionary $references, int $type): \Generator
    {
        if ($type & (1 << 4)) {
            yield from $this->waitAvailableInput($stream, 4);

            $ref = $stream->readInt32();

            if (!$references->try($ref, $value)) {
                throw new Exception();
            }

            return;
        }

        if ($type & (1 << 5)) {
            yield from $this->waitAvailableInput($stream, 4);

            $ref = $stream->readInt32();

            if (!$references->try($ref, $class)) {
                throw new Exception();
            }
        } else {
            yield from $this->doDeserializeString($stream, $class);

            $references->add($references->getLength(), $class);
        }

        /** @var IExtension $extension */
        if ($this->extensions->try($class, $extension)) {
            yield from $extension->deserialize(
                $stream,
                $value,
                new Delegate(
                    null,
                    function () use ($stream, $references) {
                        yield from $this->doDeserialize($stream, $value, $references);

                        return $value;
                    }
                ),
                new Delegate(
                    null,
                    function (int $length) use ($stream) {
                        yield from $this->waitAvailableInput($stream, $length);
                    }
                ),
                new Delegate(
                    null,
                    function ($value) use ($references) {
                        $references->add($references->getLength(), $value);
                    }
                )
            );

            $references->add($references->getLength(), $value);
        } else {
            $reflectionClass = new \ReflectionClass($class);
            $value = $reflectionClass->newInstanceWithoutConstructor();
            $references->add($references->getLength(), $value);

            $size = yield from $this->decode7BitInt($stream);

            if ($value instanceof ISerializable) {
                $info = new SerializationInfo();

                for ($i = 0; $i < $size; $i++) {
                    yield from $this->doDeserialize($stream, $key, $references);
                    yield from $this->doDeserialize($stream, $val, $references);

                    $info->add($key, $val);
                }

                $value->setObjectData($info);
            } else {
                for ($i = 0; $i < $size; $i++) {
                    yield from $this->doDeserialize($stream, $key, $references);
                    yield from $this->doDeserialize($stream, $val, $references);

                    try {
                        $property = $reflectionClass->getProperty($key);
                    } catch (\ReflectionException $exception) {
                        throw new Exception();
                    }

                    $property->setAccessible(true);
                    $property->setValue($value, $val);
                }
            }
        }
    }

    private function decode7BitInt(IInputStream $stream): \Generator
    {
        $value = 0;
        $shift = 0;

        do {
            yield from $this->waitAvailableInput($stream, 1);
            $low = $stream->readInt8();
            $value |= ($low & 0x7F) << $shift;
            $shift += 7;
        } while (($low & 0x80) !== 0);

        return $value;
    }
}
