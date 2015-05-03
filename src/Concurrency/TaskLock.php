<?php

namespace Orolyn\Concurrency;

class TaskLock
{
    public function __construct(
        public ?Task $task,
        public bool $released = false
    ) {
    }
}
