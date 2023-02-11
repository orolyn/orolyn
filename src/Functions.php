<?php
namespace Orolyn;

use Orolyn\Collection\ArrayList;
use Orolyn\Concurrency\Coroutine;
use Orolyn\Concurrency\Task;
use Orolyn\Concurrency\TaskLock;
use Orolyn\Concurrency\TaskScheduler;

/**
 * Creates and starts a task.
 *
 * @param callable $callback
 * @return Task
 */
function Async(callable $callback): Task
{
    return TaskScheduler::getInstance()->createTask($callback);
}

/**
 * Wait for task to complete.
 *
 * @param callable|Task ...$tasks
 */
function Await(callable|Task $task): mixed
{
    if (!$task instanceof Task) {
        $task = TaskScheduler::getInstance()->createTask($task);
    }

    return TaskScheduler::getInstance()->awaitTask($task);
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
    TaskScheduler::getInstance()->suspend($delay);
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

function StaticConstruct(?string $class = null): void
{
    static $initialized = [];

    if (null === $class) {

    }

    if (isset($initialized[$class])) {
        return;
    }

    $initialized[$class] = true;
    $reflection = Reflection::getReflectionClass($class);

    if (!$reflection->hasMethod('__static_construct')) {
        return;
    }

    $reflection->getMethod('__static_construct')->invoke(null);
}
