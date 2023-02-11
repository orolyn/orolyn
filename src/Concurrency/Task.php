<?php

namespace Orolyn\Concurrency;

use Throwable;

/**
 * @template T
 */
final class Task
{
    private mixed $result = null;

    private bool $terminated = false;

    private Throwable|null $exception = null;

    public function getResult(): mixed
    {
        return $this->result;
    }

    public function getException(): ?Throwable
    {
        return $this->exception;
    }

    public function isTerminated(): bool
    {
        return $this->terminated;
    }
}
