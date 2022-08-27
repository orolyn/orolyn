<?php

namespace Orolyn\Cache;

// TODO: finish
abstract class Cache
{
    public function add(string $key, mixed $value, ?CacheItemPolicy $policy = null, ?string $regionName = null): bool
    {
        return $this->addCacheItem(new CacheItem($key, $value, $regionName), $policy);
    }

    public function addCacheItem(CacheItem $item, CacheItemPolicy $policy): bool
    {
        return $this->addOrGetExistingCacheItem($item, $policy) === $item;
    }

    public function set(string $key, mixed $value, ?CacheItemPolicy $policy = null, ?string $regionName = null): void
    {
        $this->setCacheItem(new CacheItem($key, $value, $regionName), $policy);
    }

    abstract public function setCacheItem(CacheItem $item, CacheItemPolicy $policy): void;

    public function addOrGetExisting(
        string $key,
        mixed $value,
        ?CacheItemPolicy $policy = null,
        ?string $regionName = null
    ): mixed {
        return $this->addOrGetExistingCacheItem(new CacheItem($key, $value, $regionName), $policy)->value;
    }

    abstract public function addOrGetExistingCacheItem(CacheItem $item, ?CacheItemPolicy $policy = null): CacheItem;
}
