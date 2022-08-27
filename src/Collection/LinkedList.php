<?php
namespace Orolyn\Collection;

use Orolyn\ArgumentException;
use Orolyn\ArgumentOutOfRangeException;
use Orolyn\StandardObject;
use SplDoublyLinkedList;
use function Orolyn\TypeOf;

class LinkedList implements IList
{
    private SplDoublyLinkedList $items;

    public function __construct(array $items = [])
    {
        $this->items = new SplDoublyLinkedList();

        foreach ($items as $item) {
            $this->add($item);
        }
    }

    /**
     * @inheritdoc
     */
    public function getIterator(): \Generator
    {
        foreach ($this->items as $item) {
            yield $item;
        }
    }

    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return $this->items->count();
    }

    /**
     * @inheritdoc
     */
    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    /**
     * @inheritdoc
     */
    public function offsetExists(mixed $offset): bool
    {
        if (TypeOf($offset) !== 'int') {
            throw new ArgumentException();
        }

        return $this->items->offsetExists($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet(mixed $offset): mixed
    {
        if ($this->offsetExists($offset)) {
            return $this->items[$offset];
        }

        throw new ArgumentOutOfRangeException();
    }

    /**
     * @inheritdoc
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (null === $offset) {
            $this->add($value);
        } elseif ($this->offsetExists($offset)) {
            $this->items[$offset] = $value;
        }

        throw new ArgumentOutOfRangeException();
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset(mixed $offset): void
    {
        if ($this->offsetExists($offset)) {
            unset($this->items[$offset]);
        }

        throw new ArgumentOutOfRangeException();
    }

    /**
     * @param mixed $item
     * @return void
     */
    public function add(mixed $item): void
    {
        $this->items->add($this->items->count(), $item);
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        $this->items = new SplDoublyLinkedList();
    }

    /**
     * @param mixed $item
     * @return void
     */
    public function remove(mixed $item): void
    {
        if (-1 !== $index = $this->indexOf($item)) {
            $this->removeAt($index);
        }
    }

    /**
     * @param int $index
     * @return void
     */
    public function removeAt(int $index): void
    {
        $this->items->offsetUnset($index);
    }

    /**
     * @inheritdoc
     */
    public function indexOf(mixed $item): int
    {
        $i = 0;

        foreach ($this->items as $current) {
            if ($item === $current) {
                return $i;
            }
            $i++;
        }

        return -1;
    }

    /**
     * @inheritdoc
     */
    public function contains(mixed $item): bool
    {
        return -1 !== $this->indexOf($item);
    }

    /**
     * @inheritdoc
     */
    public function join(string $delimiter = ''): string
    {
        $string = '';
        $i = 0;
        $count = $this->items->count();

        foreach ($this->items as $current) {
            $i++;

            $string .= $current;

            if ($i !== $count) {
                $string .= $delimiter;
            }
        }

        return $string;
    }

    /**
     * @inheritdoc
     */
    public function map(callable $func): static
    {
        $list = new LinkedList();

        foreach ($this->items as $item) {
            $list->add($func($item));
        }

        return $list;
    }
}
