<?php

namespace Orolyn\Concurrency;

class TaskSuspensionTime
{
    /**
     * @param float $delay The number of milliseconds of the delay.
     * @param float $timestamp The time the suspension occurred, equivalent to microtime(true)
     */
    public function __construct(
        public readonly float $delay,
        public readonly float $timestamp
    ) {
    }
}
