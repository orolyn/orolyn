<?php

namespace Orolyn;

interface IComparable
{
    /**
     * @return int
     */
    public function compareTo(mixed $value): int;
}
