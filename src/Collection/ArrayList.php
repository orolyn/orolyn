<?php
namespace Orolyn\Collection;

use Ds\Vector;
use Orolyn\ArgumentException;
use Orolyn\ArgumentOutOfRangeException;
use OutOfRangeException;
use function Orolyn\TypeOf;

/**
 * @template T
 * @extends IList<T>
 */
class ArrayList implements IList
{
    private Vector $source;

    /**
     * @param iterable|null $values
     */
    public function __construct(iterable $values = [])
    {
        $this->source = new Vector(Item::getArray($values));
    }

    /**
     * @inheritdoc
     */
    public function getIterator(): \Generator
    {
        foreach ($this->source as $item) {
            yield $item->value;
        }
    }

    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return $this->source->count();
    }

    /**
     * @inheritdoc
     */
    public function isEmpty(): bool
    {
        return $this->source->isEmpty();
    }

    /**
     * Make a copy of this ArrayList
     *
     * @return ArrayList
     */
    public function copy(): ArrayList
    {
        $list = new ArrayList();
        $list->source = $this->source->copy();

        return $list;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->source[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet(mixed $offset): mixed
    {
        try {
            return $this->source[$offset]->value;
        } catch (OutOfRangeException $exception) {
            throw new ArgumentOutOfRangeException('index');
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        try {
            if (null === $offset) {
                $this->source[] = new Item($value);
            } else {
                $this->source[$offset] = new Item($value);
            }
        } catch (OutOfRangeException) {
            throw new ArgumentOutOfRangeException('offset');
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->source[$offset]);
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        $this->source->clear();
    }

    /**
     * @param T $item
     * @return void
     */
    public function remove(mixed $item): void
    {
        if (-1 !== $index = $this->indexOf($item)) {
            unset($this->source[$index]);
        }
    }

    /**
     * @inheritdoc
     */
    public function indexOf(mixed $item): int
    {
        if (false === $index = $this->source->find(new Item($item))) {
            return -1;
        }

        return $index;
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
    public function join(?string $delimiter = null): string
    {
        return $this->source->join($delimiter);
    }

    /**
     * @inheritdoc
     */
    public function map(callable $func): static
    {
        $mapped = new ArrayList();
        $mapped->source = $this->source->map(
            function ($value) use ($func) {
                return new Item($func($value->value));
            }
        );

        return $mapped;
    }
}
