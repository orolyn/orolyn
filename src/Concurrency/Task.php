<?php

namespace Orolyn\Concurrency;

use Fiber;
use FiberError;
use Closure;
use Orolyn\AggregateException;
use Orolyn\Collection\ArrayList;
use Orolyn\Collection\HashSet;
use Throwable;
use Orolyn\InvalidOperationException;
use function Orolyn\Lang\Suspend;
/**
 * @template T
 */
final class Task
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
     * @var AggregateException|null
     */
    private AggregateException|null $exception = null;

    /**
     * @var bool
     */
    private bool $exceptionObserved = false;

    /**
     * @var TaskSuspensionTime|null
     */
    private ?TaskSuspensionTime $suspensionTime = null;

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
        return $this->completed || null !== $this->exception;
    }

    /**
     * Returns true if the task completed without throwing an exception.
     *
     * @return bool
     */
    public function isCompletedSuccessfully(): bool
    {
        return $this->completed;
    }

    /**
     * Returns true if an exception was thrown during the running of the task.
     *
     * @return bool
     */
    public function isFaulted(): bool
    {
        return null !== $this->exception;
    }

    /**
     * Returns false if the task is not currently suspended.
     *
     * @return TaskSuspensionTime|null
     */
    public function getSuspensionTime(): ?TaskSuspensionTime
    {
        return $this->suspensionTime;
    }

    /**
     * Complete execution of the task if it has not completed or has not started and return the result.
     *
     * @return T|null
     */
    public function getResult(): mixed
    {
        if ($this->completed) {
            return $this->result;
        }

        $this->wait();

        return $this->fiber->getReturn();
    }

    /**
     * Return the aggregate exception if an exception was thrown during the running of the task.
     *
     * @return Throwable|null
     */
    public function getException(): ?Throwable
    {
        if (null !== $this->exception) {
            $this->exceptionObserved = true;
        }

        return $this->exception;
    }

    /**
     * Start the task.
     *
     * @return void
     */
    public function start(): void
    {
        if ($this->fiber) {
            throw new InvalidOperationException('Task is already started.');
        }

        $this->resume();
    }

    /**
     * Resume the task.
     *
     * @return void
     */
    public function resume(): void
    {
        if ($this->completed) {
            return;
        }

        try {
            $this->suspensionTime = null;

            if (!$this->fiber) {
                $callback = $this->callback;
                $args = $this->args;

                $this->callback = null;
                $this->args = null;

                $this->fiber = new Fiber($callback);

                $this->suspensionTime = $this->fiber->start($args);
            } else {
                $this->suspensionTime = $this->fiber->resume();
            }
        } catch (Throwable $exception) {
            $this->completed = true;
            $this->exception = new AggregateException($exception);
        }

        if ($this->fiber->isTerminated()) {
            if (!$this->exception) {
                $this->result = $this->fiber->getReturn();
            }

            $this->completed = true;
        }
    }

    /**
     * Waits for completion of the task. If the task has not yet started, then it is started
     *
     * @return void
     */
    public function wait(): void
    {
        if (!$this->completed) {
            TaskScheduler::awaitTasks(new ArrayList([$this]));
        }

        if ($this->exception) {
            $this->exceptionObserved = true;
            throw $this->exception;
        }
    }

    /**
     * If the task threw an exception and the exception was not observed by calling ::wait, then the exception
     * is sent to the scheduler to run through any available unobserved exception handlers. If afterwards the
     * exception still is not observed, then the exception will be thrown when this task is garbage collected.
     */
    public function __destruct()
    {
        if ($this->exception && !$this->exceptionObserved) {
            if (TaskScheduler::getTaskScheduler()->throwUnobservedTaskException($this->exception)) {
                throw $this->exception;
            }
        }
    }
}
