<?php
namespace Orolyn\Collection;

use Ds\Map;
use Generator;

/**
 * @template TKey
 * @template TValue
 *
 * @extends IDictionary<TKey, TValue>
 */
class OrderedDictionary extends Dictionary
{
    // Semantics only Ds/Map is already ordered
}
