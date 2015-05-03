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
}
