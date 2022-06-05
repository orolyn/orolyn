<?php
namespace Orolyn\Collection;

use \Ds\Stack as DsStack;
use Orolyn\InvalidOperationException;

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

    /**
     * @inheritdoc
     */
    public function isEmpty(): bool
    {
        return $this->source->isEmpty();
    }

    public function peek(): mixed
    {
        if ($this->source->isEmpty()) {
            throw new InvalidOperationException('Stack is empty');
        }

        return $this->source->peek();
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
