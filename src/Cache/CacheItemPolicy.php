<?php

namespace Orolyn\Cache;

use DateInterval;

class CacheItemPolicy
{
    public function __construct(
        public ?DateInterval $absoluteExpiration = null,
        public CacheItemPriority $priority = CacheItemPriority::Default
    ) {
    }
}
