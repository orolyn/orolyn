<?php
namespace Orolyn\Collection;

use Orolyn\ArgumentException;
use Orolyn\ArgumentOutOfRangeException;
use function Orolyn\TypeOf;

/**
 * @template T
 * @extends StaticList<T>
 */
class ImmutableList extends StaticList
{
    /**
     * @param T[] $items
     */
    public function __construct(array $items)
    {
        parent::__construct($items, count($items));
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
     * @param callable $func
     * @return void
     */
    public function forEach (callable $func): void
    {
        foreach ($this->source as $i => $item) {
            $func($item, $i);
        }
    }
}
