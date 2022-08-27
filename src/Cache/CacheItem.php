<?php

namespace Orolyn\Cache;

class CacheItem
{
    public function __construct(
        public ?string $key = null,
        public mixed $value = null,
        public ?string $regionName = null
    ) {
    }
}
