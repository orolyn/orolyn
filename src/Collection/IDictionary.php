<?php
namespace Orolyn\Collection;

use ArrayAccess;

/**
 * @template TKey
 * @template TValue
 *
 * @extends ICollection<TValue>
 * @extends ArrayAccess<TKey, TValue>
 */
interface IDictionary extends ICollection, ArrayAccess
{
    /**
     * @return void
     */
    public function clear(): void;

    /**
     * @param TKey $key
     * @return void
     */
    public function remove(mixed $key): void;

    /**
     * @param TKey $key
     * @return TValue
     */
    public function get(mixed $key): mixed;

    /**
     * @param TKey $key
     * @param TValue $value
     * @return bool
     */
    public function try(mixed $key, mixed &$value): bool;

    /**
     * @param TKey $key
     * @param TValue $value
     * @return void
     * @throws KeyAlreadyExistsException
     */
    public function add(mixed $key, mixed $value): void;

    /**
     * @param TKey $key
     * @param TValue $value
     * @return void
     */
    public function set(mixed $key, mixed $value): void;

    /**
     * @param TKey $key
     * @return bool
     */
    public function containsKey(mixed $key): bool;

    /**
     * @param TValue $value
     * @return bool
     */
    public function contains(mixed $value): bool;

    /**
     * @return IList<TValue>
     */
    public function getKeys(): IList;

    /**
     * @return IList<TValue>
     */
    public function getValues(): IList;
}
