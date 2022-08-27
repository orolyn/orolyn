<?php
namespace Orolyn\Collection;

use Ds\Set;
use SplObjectStorage;

/**
 * @template T
 * @extends HashSet<T>
 */
class OrderedSet extends HashSet
{
    // Semantics only Ds/Set is already ordered
}
