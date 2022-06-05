<?php
namespace Orolyn\Collection;

use ArrayAccess;
use IteratorAggregate;

/**
 * @template T
 * @extends ICollection<T>
 * @extends ArrayAccess<int, T>
 */
interface IList extends ICollection, ArrayAccess
{
    /**
     * @param T $item
     * @return int
     */
    public function indexOf(mixed $item): int;

    /**
     * @param T $item
     * @return bool
     */
    public function contains(mixed $item): bool;

    /**
     * @param string $delimiter
     * @return string
     */
    public function join(string $delimiter): string;

    /**
     * @param callable $func
     * @return static
     */
    public function map(callable $func): static;
}
