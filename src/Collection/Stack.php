<?php
namespace Orolyn\Collection;

use \Ds\Stack as DsStack;

class Stack implements ICollection
{
    private DsStack $source;

    public function __construct(iterable $source = [])
    {
        $this->source = $source instanceof DsStack ? $source : new DsStack($source);
    }

    public function count(): int
    {
        return $this->source->count();
    }

    public function getIterator(): \Generator
    {
        foreach ($this->source as $item) {
            return $item;
        }
    }

    public function peek(): mixed
    {
        $this->source->peek();
    }

    public function push(mixed $item): void
    {
        $this->source->push($item);
    }

    public function pop(): mixed
    {
        return $this->source->pop();
    }
}
