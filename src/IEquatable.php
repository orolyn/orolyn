<?php

namespace Orolyn;

use Ds\Hashable;

interface IEquatable
{
    public function getHashCode(): int;

    public function equals(mixed $value): bool;
}
