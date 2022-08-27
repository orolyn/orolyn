<?php

namespace Orolyn\Concurrency;

use Fiber;
use FiberError;
use Closure;
use Orolyn\AggregateException;
use Orolyn\Collection\ArrayList;
use Orolyn\Collection\HashSet;
use Orolyn\Collection\Stack;
use Throwable;
use Orolyn\InvalidOperationException;
use function Orolyn\Suspend;

/**
 * @template T
 */
final class Coroutine
{
    /**
     * @var Fiber|null
     */
    private ?Fiber $fiber = null;

    /**
     * @var Closure|null
     */
    private ?Closure $callback;

    /**
     * @var array|null
     */
    private ?array $args;

    /**
     * @var bool
     */
    private bool $completed = false;

    /**
     * @var mixed|null
     */
    private mixed $result = null;

    /**
     * @param mixed $callback
     * @param ...$args
     */
    public function __construct(mixed $callback, ...$args)
    {
        $this->callback = $callback(...);
        $this->args = $args;
    }

    /**
     * Returns true if the task completed, whether by successful execution or by throwing an exception.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->completed;
    }

    /**
     * Complete execution of the task if it has not completed or has not started and return the result.
     *
     * @return T|null
     */
    public function getResult(): mixed
    {
        while (!$this->completed) {
            $this->resume();
        }

        return $this->result;
    }

    /**
     * Start the task.
     *
     * @return void
     */
    public function start(): void
    {
        if ($this->fiber) {
            throw new InvalidOperationException('Coroutine is already started.');
        }

        $this->resume();
    }

    /**
     * Resumes the coroutine
     *
     * @return void
     */
    public function resume(): void
    {
        if ($this->completed) {
            return;
        }

        if (!$this->fiber) {
            $callback = $this->callback;
            $args = $this->args;

            $this->callback = null;
            $this->args = null;

            $this->fiber = new Fiber($callback);

            $this->fiber->start($args);
        } else {
            $this->fiber->resume();
        }

        if ($this->fiber->isTerminated()) {
            $this->result = $this->fiber->getReturn();
            $this->completed = true;
        }
    }
}
