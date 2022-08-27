<?php
namespace Orolyn\Collection;

use \Ds\Stack as DsStack;
use Orolyn\InvalidOperationException;

/**
 * @template T
 * @extends IStack<T>
 */
class Stack implements IStack
{
    private DsStack $source;

    public function __construct(iterable $source = [])
    {
        $this->source = $source instanceof DsStack ? $source : new DsStack($source);
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

    /**
     * @inheritdoc
     */
    public function peek(): mixed
    {
        if ($this->source->isEmpty()) {
            throw new InvalidOperationException('Stack is empty');
        }

        return $this->source->peek();
    }

    /**
     * @inheritdoc
     */
    public function push(mixed $item): void
    {
        $this->source->push($item);
    }

    /**
     * @inheritdoc
     */
    public function pop(): mixed
    {
        if ($this->source->isEmpty()) {
            throw new InvalidOperationException('Stack is empty');
        }

        return $this->source->pop();
    }
}
