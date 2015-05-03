<?php

namespace Orolyn;

use Orolyn\Collection\ArrayList;
use Orolyn\Collection\ICollection;
use Orolyn\Collection\IList;
use Traversable;

/**
 * @extends ICollection<callback(EventArgs):void>
 */
class EventHandler implements ICollection
{
    /**
     * @var ArrayList<callback(EventArgs):void>
     */
    private ArrayList $callbacks;

    public function __construct()
    {
        $this->callbacks = new ArrayList();
    }

    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return $this->callbacks->count();
    }

    /**
     * @inheritdoc
     */
    public function getIterator(): Traversable
    {
        foreach ($this->callbacks as $callback) {
            yield $callback;
        }
    }

    /**
     * @param callable(EventArgs):void $callback
     * @return void
     */
    public function add(mixed $callback)
    {
        if (!is_callable($callback)) {
            throw new ArgumentException('Argument is not a callable value.');
        }

        $this->callbacks[] = $callback;
    }

    /**
     * @param EventArgs $eventArgs
     * @return void
     */
    public function __invoke(EventArgs $eventArgs): void
    {
        $this->invoke($eventArgs);
    }

    /**
     * @param EventArgs $eventArgs
     * @return void
     */
    public function invoke(EventArgs $eventArgs): void
    {
        foreach ($this->callbacks as $callback) {
            $callback($eventArgs);
        }
    }
}
