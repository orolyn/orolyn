<?php
namespace Orolyn\Lang;

use Orolyn\AggregateException;
use Orolyn\ArgumentException;
use Orolyn\Collection\ArrayList;
use Orolyn\Collection\StaticList;
use Orolyn\Concurrency\Task;
use Orolyn\Concurrency\TaskLock;
use Orolyn\Concurrency\TaskScheduler;

/**
 * @param callable $callback
 * @return Task
 */
function Async(callable $callback): Task
{
    $task = new Task($callback);
    $task->start();

    return $task;
}

/**
 * Wait for one or more tasks to complete.
 *
 * @param callable|iterable|Task ...$tasks
 * @return void
 */
function Await(callable|iterable|Task ...$tasks): void
{
    $collection = [];

    foreach ($tasks as $task) {
        if ($task instanceof Task) {
            $collection[] = $task;
        } elseif (is_callable($task)) {
            $collection[] = new Task($task);
        } else {
            foreach ($task as $thisTask) {
                if (is_callable($thisTask)) {
                    $thisTask = new Task($thisTask);
                } elseif (!$thisTask instanceof Task) {
                    throw new ArgumentException('Iterable collection must only contain tasks or callables.');
                }

                $collection[] = $thisTask;
            }
        }
    }

    if (1 === count($collection)) {
        $collection[0]->wait();
    }

    if (count($collection) > 0) {
        TaskScheduler::awaitTasks(new ArrayList($collection));
    }

    $exceptions = [];

    foreach ($collection as $task) {
        if ($exception = $task->getException()) {
            $exceptions[] = $exception;
        }
    }

    if (count($exceptions) > 0) {
        throw new AggregateException(null, StaticList::createImmutableList($exceptions));
    }
}

/**
 * Suspend the task. If the specified delay in milliseconds is greater than zero, then the suspension will last
 * that long at a minimum.
 *
 * @param float $delay
 * @return void
 */
function Suspend(float $delay = 0): void
{
    TaskScheduler::suspend($delay);
}

/**
 * @param TaskLock|null $lock
 * @param callable|null $func
 * @return mixed
 */
function Lock(?TaskLock &$lock, ?callable $func = null): mixed
{
    return TaskScheduler::getTaskScheduler()->lock($lock, $func);
}

/**
 * @param TaskLock|null $lock
 * @return void
 */
function Unlock(?TaskLock &$lock): void
{
    TaskScheduler::getTaskScheduler()->unlock($lock);
}
