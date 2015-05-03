<?php
namespace Orolyn\Collection;

use Orolyn\ArgumentException;
use Orolyn\ArgumentOutOfRangeException;
use function Orolyn\Lang\TypeOf;

/**
 * @template T
 * @extends IList<T>
 */
class EmptyList implements IList
{
    /**
     * @inheritdoc
     */
    public function getIterator(): \Generator
    {
        foreach ([] as $value) {
            yield $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return 0;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset): mixed
    {
        throw new ArgumentOutOfRangeException();
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value): void
    {
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset): void
    {
    }

    /**
     * @inheritDoc
     */
    public function indexOf(mixed $item): int
    {
        return -1;
    }

    /**
     * @inheritDoc
     */
    public function contains(mixed $value): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function join(string $delimiter): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function map(callable $func): static
    {
        return new EmptyList();
    }
}
