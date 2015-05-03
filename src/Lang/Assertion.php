<?php

namespace Orolyn\Lang;

use Orolyn\Collection\IDictionary;

/**
 * Return true if the collection contains values of the specified type.
 *
 * @param string|array $type
 * @param iterable $iterable
 * @return bool
 */
function AssertCollectionContains(string|array $type, iterable $iterable): bool
{
    if (is_string($type)) {
        $baseObject = 'object' === $type;

        foreach ($iterable as $item) {
            if ($baseObject && (TypeOf($item) === 'object')) {
                continue;
            }

            if (TypeOf($item, TYPE_OF_OBJECT) !== $type) {
                return false;
            }
        }

        return true;
    }

    $baseObject = in_array('object', $type);

    foreach ($iterable as $item) {
        if ($baseObject && (TypeOf($item) === 'object')) {
            continue;
        }

        if (!in_array(TypeOf($item, TYPE_OF_OBJECT), $type)) {
            return false;
        }
    }

    return true;
}
