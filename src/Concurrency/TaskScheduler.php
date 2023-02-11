<?php
declare(ticks = 1);

namespace Orolyn\Concurrency;

use Generator;
use Orolyn\AggregateException;
use Orolyn\Collection\ArrayList;
use Fiber;
use Orolyn\Collection\Dictionary;
use Orolyn\Collection\LinkedList;
use Orolyn\Collection\StaticList;
use Orolyn\Reflection;
use Throwable;
use function Orolyn\StaticConstruct;

final class TaskScheduler
{
    /**
     * @var Generator<int, null, null, Task>
     */
    private Generator $generator;

    /**
     * @var Dictionary<Task, ?Fiber>
     */
    private Dictionary $source;

    private function __construct()
    {
        $this->source = new Dictionary();
        $this->source->add(new Task(), null);
        $this->generator = $this->createTaskGenerator();
    }

    public static function getInstance(): TaskScheduler
    {
        static $scheduler;

        if (null === $scheduler) {
            $scheduler = new TaskScheduler();
        }

        return $scheduler;
    }

    public function getTaskLength(): int
    {
        return $this->source->count();
    }

    public function createTask(mixed $callback): Task
    {
        $task = new Task();
        $fiber = new Fiber($callback);
        $this->source->add($task, $fiber);
        $this->resume($task);

        return $task;
    }

    /**
     * @internal
     */
    public function suspend(): void
    {
        if (Fiber::getCurrent()){
            Fiber::suspend();
        }

        $this->generator->next();
        $task = $this->generator->current();

        $this->resume($task);
    }

    public function resume(Task $task): void
    {
        static $taskException;
        static $taskTerminated;
        static $taskResult;

        $taskException = $taskException ?? Reflection::getReflectionClass(Task::class)->getProperty('exception');
        $taskTerminated = $taskTerminated ?? Reflection::getReflectionClass(Task::class)->getProperty('terminated');
        $taskResult = $taskResult ?? Reflection::getReflectionClass(Task::class)->getProperty('result');

        if (null === $fiber = $this->source->get($task)) {
            return;
        }

        try {
            if (!$fiber->isStarted()) {
                $fiber->start();
            } else {
                $fiber->resume();
            }
        } catch (Throwable $exception) {
            $taskException->setValue($task, $exception);
        }

        if ($fiber->isTerminated()) {
            $taskTerminated->setValue($task, true);
            $taskResult->setValue($task, $fiber->getReturn());
            $this->source->remove($task);
        }
    }

    /**
     * @internal
     */
    public function awaitTask(Task $task): mixed
    {
        while (!$task->isTerminated()) {
            $this->suspend();
        }

        if ($exception = $task->getException()) {
            throw $exception;
        }

        return $task->getResult();
    }

    /**
     * @internal
     *
     * @param TaskLock|null $target
     * @param callable|null $func
     * @return mixed
     */
    public static function lock(?TaskLock &$target, ?callable $func = null): mixed
    {
        $task = null;

        while (null !== $target && !$target->released) {
            self::suspend();
        }

        $target = new TaskLock($task);

        if (null !== $func) {
            try {
                return $func();
            } finally {
                self::unlock($target);
            }
        }

        return null;
    }

    /**
     * @internal
     *
     * @param TaskLock|null $target
     * @return void
     */
    public static function unlock(?TaskLock &$target): void
    {
        if (null === $target || $target->released) {
            return;
        }

        $target->task = null;
        $target->released = true;
        $target = null;
    }

    private function createTaskGenerator(): Generator
    {
        for (;;) {
            foreach ($this->source as $task => $fiber) {
                yield $task;
            }

            usleep(100);
        }
    }
}
