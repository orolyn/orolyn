<?php
namespace Orolyn\Collection;

use SplObjectStorage;
use Generator;

/**
 * @template TKey
 * @template TValue
 *
 * @extends IDictionary<TKey, TValue>
 */
class EmptyDictionary implements IDictionary
{
    /**
     * @inheritDoc
     */
    public function getIterator(): Generator
    {
        foreach ([] as $value) {
            yield $value;
        }
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function offsetExists(mixed $offset): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function offsetGet(mixed $offset): mixed
    {
        throw new KeyNotFoundException();
    }

    /**
     * @inheritDoc
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset(mixed $offset): void
    {
    }

    /**
     * @inheritDoc
     */
    public function clear(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function remove(mixed $key): void
    {
    }

    /**
     * @inheritDoc
     */
    public function get(mixed $key): mixed
    {
        throw new KeyNotFoundException();
    }

    /**
     * @inheritDoc
     */
    public function try(mixed $key, mixed &$value): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function add(mixed $key, mixed $value): void
    {
    }

    /**
     * @inheritDoc
     */
    public function set(mixed $key, mixed $value): void
    {
    }

    /**
     * @inheritDoc
     */
    public function containsKey(mixed $key): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function contains(mixed $value): bool
    {
        return false;
    }

    /**
     * @return IList<TValue>
     */
    public function getKeys(): IList
    {
        return new EmptyList();
    }

    /**
     * @return IList<TValue>
     */
    public function getValues(): IList
    {
        return new EmptyList();
    }
}
