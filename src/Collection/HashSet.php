<?php
namespace Orolyn\Collection;

use Ds\Set;
use SplObjectStorage;

/**
 * @template T
 * @extends ICollection<T>
 */
class HashSet implements ICollection
{
    protected Set $source;

    /**
     * @param iterable<T> $values
     */
    public function __construct(iterable $values = [])
    {
        $this->source = new Set(Item::getArray($values));
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
    public function getIterator(): \Generator
    {
        foreach ($this->source as $item) {
            yield $item->value;
        }
    }

    /**
     * @inheritdoc
     */
    public function isEmpty(): bool
    {
        return $this->source->isEmpty();
    }

    /**
     * @param mixed $item
     * @return void
     */
    public function add(mixed $item): void
    {
        $this->source->add(new Item($item));
    }

    /**
     * @param $item
     * @return void
     */
    public function remove($item): void
    {
        $this->source->remove(new Item($item));
    }

    /**
     * @param $item
     * @return bool
     */
    public function contains($item): bool
    {
        return $this->source->contains(new Item($item));
    }

    public function copy(): HashSet
    {
        $hashSet = new static();
        $hashSet->source = $this->source->copy();

        return $hashSet;
    }

    public function toArray(): array
    {
        return $this->source->toArray();
    }
}
