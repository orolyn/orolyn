<?php
declare(ticks = 1);

namespace Orolyn\Concurrency;

use Orolyn\AggregateException;
use Orolyn\Collection\ArrayList;
use Fiber;
use Orolyn\Collection\StaticList;

final class TaskScheduler
{
    /**
     * @var Application|null
     */
    private static ?Application $application = null;

    /**
     * Run a managed application. All created tasks will be added to the scheduler and run in a loop until
     * all tasks have completed, then it will terminate.
     *
     * @param Application $application
     * @return void
     */
    public static function run(Application $application): void
    {
        static $pcntl;

        if (null === $pcntl) {
            foreach ([SIGTERM, SIGINT, SIGHUP] as $signal) {
                pcntl_signal($signal, self::terminate(...));
            }

            $pcntl = true;
        }

        self::$application = $application;

        $task = new Task(fn () => $application->main());
        $task->start();

        self::awaitTasks(new ArrayList([$task]));
        $application->terminate();
    }

    /**
     * @internal
     *
     * @param AggregateException $exception
     * @return bool
     */
    public static function throwUnobservedTaskException(AggregateException $exception): bool
    {
        $eventArgs = new UnobservedTaskExceptionEventArgs($exception);
        self::$application?->onUnobservedTaskException($eventArgs);

        return !$eventArgs->isObserved();
    }

    /**
     * @internal
     *
     * @return bool
     */
    public static function isRunningApplication(): bool
    {
        return null !== self::$application;
    }

    /**
     * @internal
     *
     * @param ArrayList<Task> $tasks
     * @return void
     */
    public static function awaitTasks(ArrayList $tasks): void
    {
        $exceptions = [];

        for (;;) {
            $time = microtime(true);

            foreach ($tasks as $task) {
                if ($task->isCompleted()) {
                    if ($exception = $task->getException()) {
                        $exceptions[] = $exception;
                    }

                    $tasks->remove($task);
                    continue;
                }

                if (($suspensionTime = $task->getSuspensionTime()) && $suspensionTime->delay > 0) {
                    if (($time - $suspensionTime->timestamp) * 1000 < $suspensionTime->delay) {
                        continue;
                    }
                }

                $task->resume();
            }

            if ($tasks->count() < 1) {
                break;
            }

            self::suspend();
        }

        if (count($exceptions) > 0) {
            throw new AggregateException(null, StaticList::createImmutableList($exceptions));
        }
    }

    /**
     * @internal
     *
     * @return void
     */
    public static function suspend(float $delay = 0): void
    {
        if (Fiber::getCurrent()) {
            Fiber::suspend(new TaskSuspensionTime($delay, microtime(true)));
        } else {
            self::sleep($delay);
        }
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

    /**
     * @param float $delay
     * @return void
     */
    private static function sleep(float $delay = 0): void
    {
        if ($delay > 0) {
            usleep($delay * 1000);
        } else {
            usleep(100);
        }
    }

    /**
     * @return void
     */
    private static function terminate(): void
    {
        self::$application?->terminate();

        exit;
    }
}
