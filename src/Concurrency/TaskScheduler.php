<?php
declare(ticks = 1);

namespace Orolyn\Concurrency;

use Orolyn\AggregateException;
use Orolyn\Collection\ArrayList;
use Orolyn\Collection\HashSet;
use Fiber;
use Orolyn\Collection\Stack;
use Orolyn\EventHandler;
use Orolyn\Lang\InternalCaller;
use Orolyn\RuntimeException;
use function Orolyn\Lang\Suspend;

final class TaskScheduler
{
    /**
     * @var Application|null
     */
    private ?Application $application = null;

    /**
     * @var EventHandler
     */
    private EventHandler $unobservedExceptionsHandler;

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->unobservedExceptionsHandler = new EventHandler();
        $this->unobservedExceptionsHandler->add($this->onUnobservedTaskException(...));

        foreach ([SIGTERM, SIGINT, SIGHUP] as $signal) {
            pcntl_signal($signal, $this->terminate(...));
        }
    }

    /**
     * Run a managed application. All created tasks will be added to the scheduler and run in a loop until
     * all tasks have completed, then it will terminate.
     *
     * @param Application $application
     * @return void
     */
    public static function run(Application $application): void
    {
        $scheduler = self::getTaskScheduler();
        $scheduler->application = $application;

        $task = new Task(fn () => $application->main());
        $task->start();

        $scheduler->awaitTasks(new ArrayList([$task]));
        $application->terminate();
    }

    /**
     * @param callable(UnobservedTaskExceptionEventArgs):void $callback
     * @return void
     */
    public static function addUnobservedTaskExceptionHandler(callable $callback): void
    {
        self::getTaskScheduler()->unobservedExceptionsHandler->add($callback);
    }

    /**
     * @internal
     *
     * @return TaskScheduler
     */
    public static function getTaskScheduler(): TaskScheduler
    {
        static $instance;

        if (null === $instance) {
            $instance = new TaskScheduler();
        }

        return $instance;
    }

    /**
     * @internal
     *
     * @param AggregateException $exception
     * @return bool
     */
    public function throwUnobservedTaskException(AggregateException $exception): bool
    {
        $eventArgs = new UnobservedTaskExceptionEventArgs($exception);
        $this->unobservedExceptionsHandler->invoke($eventArgs);

        return !$eventArgs->isObserved();
    }

    /**
     * @internal
     *
     * @return bool
     */
    public function isRunningApplication(): bool
    {
        return null !== $this->application;
    }

    /**
     * @internal
     *
     * @param ArrayList<Task> $tasks
     * @return void
     */
    public static function awaitTasks(ArrayList $tasks): void
    {
        for (;;) {
            $time = microtime(true);

            foreach ($tasks as $task) {
                if ($task->isCompleted()) {
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
    }

    /**
     * @internal
     *
     * @return void
     */
    public static function suspend(float $delay = 0): void
    {
        $currentFiber = Fiber::getCurrent();

        if ($currentFiber) {
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
     * @return void
     */
    public function lock(?TaskLock &$target, ?callable $func = null): mixed
    {
        $task = null;

        while (null !== $target && !$target->released) {
            $this->suspend();
        }

        $target = new TaskLock($task);

        if (null !== $func) {
            try {
                return $func();
            } finally {
                $this->unlock($target);
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
    public function unlock(?TaskLock &$target): void
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
    private function terminate(): void
    {
        $this->application?->terminate();

        exit;
    }

    /**
     * @param UnobservedTaskExceptionEventArgs $eventArgs
     * @return void
     */
    private function onUnobservedTaskException(UnobservedTaskExceptionEventArgs $eventArgs): void
    {
        $this->application?->onUnobservedTaskException($eventArgs);
    }
}
