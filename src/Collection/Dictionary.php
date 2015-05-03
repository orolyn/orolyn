<?php
namespace Orolyn\Collection;

use Ds\Map;
use Generator;

/**
 * @template TKey
 * @template TValue
 *
 * @extends IDictionary<TKey, TValue>
 */
class Dictionary implements IDictionary
{
    /**
     * @var Map
     */
    private Map $source;

    /**
     * @param iterable $source
     */
    public function __construct(iterable $source = [])
    {
        $this->source = $source instanceof Map ? $source : new Map($source);
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Generator
    {
        foreach ($this->source as $key => $object) {
            yield $key => $object;
        }
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return $this->source->count();
    }

    /**
     * @inheritDoc
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->source[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->remove($offset);
    }

    /**
     * @inheritDoc
     */
    public function clear(): void
    {
        $this->source->clear();
    }

    /**
     * @inheritDoc
     */
    public function remove(mixed $key): void
    {
        $this->source->remove($key);
    }

    /**
     * @inheritDoc
     */
    public function get(mixed $key): mixed
    {
        if (!$this->try($key, $value)) {
            throw new KeyNotFoundException();
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function try(mixed $key, mixed &$value): bool
    {
        if (!$this->source->hasKey($key)) {
            return false;
        }

        $value = $this->source->get($key);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function add(mixed $key, mixed $value): void
    {
        if ($this->source->hasKey($key)) {
            throw new KeyAlreadyExistsException();
        }

        $this->source->put($key, $value);
    }

    /**
     * @inheritDoc
     */
    public function set(mixed $key, mixed $value): void
    {
        $this->source->put($key, $value);
    }

    /**
     * @inheritDoc
     */
    public function containsKey(mixed $key): bool
    {
        return $this->source->hasKey($key);
    }

    /**
     * @inheritDoc
     */
    public function contains(mixed $value): bool
    {
        return $this->source->hasValue($value);
    }

    /**
     * @inheritDoc
     */
    public function getKeys(): IList
    {
        return new ArrayList($this->source->keys());
    }

    /**
     * @inheritDoc
     */
    public function getValues(): IList
    {
        return new ArrayList($this->source->values());
    }
}
