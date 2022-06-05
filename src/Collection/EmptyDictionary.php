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
     * @inheritdoc
     */
    public function getIterator(): Generator
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
    public function isEmpty(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists(mixed $offset): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function offsetGet(mixed $offset): mixed
    {
        throw new KeyNotFoundException();
    }

    /**
     * @inheritdoc
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset(mixed $offset): void
    {
    }

    /**
     * @inheritdoc
     */
    public function clear(): void
    {
    }

    /**
     * @inheritdoc
     */
    public function remove(mixed $key): void
    {
    }

    /**
     * @inheritdoc
     */
    public function get(mixed $key): mixed
    {
        throw new KeyNotFoundException();
    }

    /**
     * @inheritdoc
     */
    public function try(mixed $key, mixed &$value): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function add(mixed $key, mixed $value): void
    {
    }

    /**
     * @inheritdoc
     */
    public function set(mixed $key, mixed $value): void
    {
    }

    /**
     * @inheritdoc
     */
    public function containsKey(mixed $key): bool
    {
        return false;
    }

    /**
     * @inheritdoc
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
