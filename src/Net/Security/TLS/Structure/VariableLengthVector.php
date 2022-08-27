<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Countable;
use IteratorAggregate;
use Orolyn\Collection\ArrayList;
use Orolyn\Collection\HashSet;
use Orolyn\Collection\OrderedSet;
use Orolyn\IO\ByteStream;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;
use Traversable;

/**
 * @template T
 * @extends IteratorAggregate<T>
 */
abstract class VariableLengthVector extends Structure implements IteratorAggregate, Countable
{
    /**
     * @var class-string<Structure>
     */
    protected static string $structureClass;

    /**
     * @var VariableLength
     */
    protected static VariableLength $variableLength;

    /**
     * @var OrderedSet<T>
     */
    protected OrderedSet $source;

    /**
     * @param Structure[] $source
     */
    final public function __construct(
        iterable $source
    ) {
        $this->source = new OrderedSet($source);
    }

    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return count($this->source);
    }

    /**
     * @return Traversable<T>
     */
    public function getIterator(): Traversable
    {
        foreach ($this->source as $item) {
            yield $item;
        }
    }

    /**
     * @inheritdoc
     */
    public function encode(IOutputStream $stream): void
    {
        $byteStream = self::createByteStream();

        foreach ($this->source as $structure) {
            $structure->encode($byteStream);
        }

        $length = $byteStream->getLength();

        switch (static::$variableLength) {
            case VariableLength::UInt8: $stream->writeUnsignedInt8($length);
                break;
            case VariableLength::UInt16: $stream->writeUnsignedInt16($length);
                break;
            case VariableLength::UInt24: $stream->writeUnsignedInt24($length);
                break;
            case VariableLength::UInt32: $stream->writeUnsignedInt32($length);
                break;
        }

        $stream->write($byteStream);
    }

    /**
     * @inheritdoc
     */
    public static function decode(IInputStream $stream, ?bool $server = null): static
    {
        $length = match (static::$variableLength) {
            VariableLength::UInt8  => $stream->readUnsignedInt8(),
            VariableLength::UInt16 => $stream->readUnsignedInt16(),
            VariableLength::UInt24 => $stream->readUnsignedInt24(),
            VariableLength::UInt32 => $stream->readUnsignedInt32()
        };

        $byteStream = self::createByteStream($stream->read($length));

        $structures = [];

        while ($byteStream->getBytesAvailable()) {
            $structures[] = static::$structureClass::decode($byteStream, $server);
        }

        return new static($structures);
    }

    /**
     * @return bool
     */
    public function contains(IStructure $structure): bool
    {
        return $this->source->contains($structure);
    }
}
