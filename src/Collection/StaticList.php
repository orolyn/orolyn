<?php
namespace Orolyn\Collection;

use Orolyn\ArgumentException;
use Orolyn\ArgumentOutOfRangeException;
use SplFixedArray;
use function Orolyn\Lang\TypeOf;

/**
 * TODO: get rid of this
 *
 * @template T
 * @extends IList<T>
 */
class StaticList implements IList
{
    private SplFixedArray $source;
    private int $length;

    /**
     * @param iterable $items
     * @param int|null $size
     */
    public function __construct(iterable $items, ?int $size = null)
    {
        if (is_array($items)) {
            $actualSize = count($items);
            $actualList = $items;
        } else {
            $actualList = [];

            foreach ($items as $item) {
                $actualList[] = $item;
            }

            $actualSize = count($actualList);
        }

        if (null === $size) {
            $size = $actualSize;
        } elseif ($actualSize > $size) {
            throw new ArgumentException('Number of provided items is greater than the set size');
        }

        $this->source = SplFixedArray::fromArray($actualList, false);
        $this->source->setSize($size);
        $this->length = $size;
    }

    /**
     * @param array $items
     * @return IList
     */
    public static function createImmutableList(array $items): IList
    {
        $count = count($items);

        if ($count < 1) {
            return new EmptyList();
        }

        return new ImmutableList($items);
    }

    /**
     * @inheritdoc
     */
    public function getIterator(): \Generator
    {
        foreach ($this->source as $item) {
            yield $item;
        }
    }

    /**
     * @inheritdoc
     */
    public function isEmpty(): bool
    {
        return 0 === $this->source->getSize();
    }

    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return $this->source->getSize();
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset): bool
    {
        if (TypeOf($offset) !== 'int') {
            throw new ArgumentException();
        }

        return $offset >= 0 || $offset < $this->length - 1;
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset): mixed
    {
        if (!$this->offsetExists($offset)) {
            throw new ArgumentOutOfRangeException();
        }

        return $this->source[$offset];
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value): void
    {
        if (!$this->offsetExists($offset)) {
            throw new ArgumentOutOfRangeException();
        }

        $this->source[$offset] = $value;
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset): void
    {
        if (!$this->offsetExists($offset)) {
            throw new ArgumentOutOfRangeException();
        }

        $this->source[$offset] = null;
    }

    /**
     * @inheritdoc
     */
    public function indexOf(mixed $item): int
    {
        for ($i = 0; $i < $this->length; $i++) {
            if ($item === $this->source[$i]) {
                return $i;
            }
        }

        return -1;
    }

    /**
     * @inheritdoc
     */
    public function contains(mixed $value): bool
    {
        return -1 !== $this->indexOf($value);
    }

    /**
     * @inheritdoc
     */
    public function join(string $delimiter): string
    {
        return implode($delimiter, $this->source->toArray());
    }

    /**
     * @inheritdoc
     */
    public function map(callable $func): static
    {
        new static(array_map($func, $this->source->toArray()), $this->length);
    }
}
