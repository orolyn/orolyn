<?php
namespace Orolyn\Collection;
use SplQueue;

class Queue implements ICollection
{
    private SplQueue $internal;

    public function __construct(iterable $collection = null)
    {
        $this->internal = new SplQueue();

        if (null !== $collection) {
            foreach ($collection as $item) {
                $this->enqueue($item);
            }
        }
    }

    public function count(): int
    {
        return $this->internal->count();
    }

    public function getIterator(): \Generator
    {
        foreach ($this->internal as $item) {
            return $item;
        }
    }

    public function enqueue($item): void
    {
        $this->internal->enqueue($item);
    }

    public function dequeue()
    {
        return $this->internal->dequeue();
    }
}
