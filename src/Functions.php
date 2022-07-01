<?php
namespace Orolyn;

use Orolyn\ArgumentException;
use Orolyn\Collection\ArrayList;
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

        return;
    }

    if (count($collection) > 1) {
        TaskScheduler::awaitTasks(new ArrayList($collection));
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
    return TaskScheduler::lock($lock, $func);
}

/**
 * @param TaskLock|null $lock
 * @return void
 */
function Unlock(?TaskLock &$lock): void
{
    TaskScheduler::unlock($lock);
}

define('TYPE_OF_NATIVE', 1);
define('TYPE_OF_OBJECT', 2);

function TypeOf($value, int $flags = 0): ?string
{
    switch (gettype($value)) {
        case 'boolean' : return 'bool';
        case 'integer' : return 'int';
        case 'double'  : return 'float';
        case 'float'   : return 'float';
        case 'string'  : return 'string';
        case 'array'   : return 'array';
        case 'resource': return 'pointer';
        case 'NULL'    : return 'null';
        case 'object':
            if ($flags & TYPE_OF_OBJECT) {
                return get_class($value);
            }

            return 'object';
    }

    if (is_callable($value)) {
        return 'closure';
    }

    return null;
}

function VarDumpBinary(string $string): void
{
    for ($i = 0; $i < strlen($string); $i++) {
        var_dump(str_pad(decbin(hexdec(bin2hex($string[$i]))), 8, '0', STR_PAD_LEFT));
    }
}
