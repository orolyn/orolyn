<?php
namespace Orolyn\Collection;

use \Ds\Stack as DsStack;
use Orolyn\InvalidOperationException;

/**
 * @template T
 * @extends ICollection<T>
 */
interface IStack extends ICollection
{
    /**
     * @return T
     */
    public function peek(): mixed;

    /**
     * @param T $item
     * @return void
     */
    public function push(mixed $item): void;

    /**
     * @return T
     */
    public function pop(): mixed;
}
