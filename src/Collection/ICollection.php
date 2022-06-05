<?php
namespace Orolyn\Collection;

use IteratorAggregate;
use Countable;

/**
 * @template T
 * @extends IteratorAggregate<T>
 */
interface ICollection extends IteratorAggregate, Countable
{
    /**
     * Returns true if this collection is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool;
}
