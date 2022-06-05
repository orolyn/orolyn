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
     * @var ArrayList<Task>
     */
    private ArrayList $tasks;

    /**
     * @var EventHandler
     */
    private EventHandler $unobservedExceptionsHandler;

    /**
     * @var Stack
     */
    private Stack $taskStack;

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->tasks = new ArrayList();
        $this->taskStack = new Stack();

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

        $scheduler->bootApplication($application);
        $scheduler->awaitTasks($scheduler->tasks);

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
     * @param Task $task
     * @return void
     */
    public function addTask(Task $task): void
    {
        if (null !== $this->application) {
            $this->tasks[] = $task;
        }
    }

    /**
     * @internal
     *
     * @param ArrayList<Task> $tasks
     * @return void
     */
    public function awaitTasks(ArrayList $tasks): void
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

                $this->resumeTask($task);
            }

            if ($tasks->count() < 1) {
                break;
            }

            if ($this->tasks === $tasks || null === $this->application) {
                $this->sleep();
            } else {
                $this->suspend();
            }
        }
    }

    public function resumeTask(Task $task): void
    {
        $this->taskStack->push($task);

        InternalCaller::callMethod($task, 'progress');

        if ($task !== $this->taskStack->pop()) {
            throw new TaskSchedulerException('Task stack is in an invalid state');
        }
    }

    /**
     * @internal
     *
     * @return void
     */
    public function suspend(float $delay = 0): void
    {
        $currentFiber = Fiber::getCurrent();

        if ($currentFiber) {
            Fiber::suspend(new TaskSuspensionTime($delay, microtime(true)));
        } else {
            $this->sleep($delay);
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

        if (!$this->taskStack->isEmpty() && $task = $this->taskStack->peek()) {
            while (null !== $target && !$target->released) {
                $this->suspend();
            }
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
     * @param Application $application
     * @return void
     */
    private function bootApplication(Application $application): void
    {
        $this->application = $application;

        $task = new Task(fn () => $application->main());
        $task->start();
    }

    /**
     * @param float $delay
     * @return void
     */
    private function sleep(float $delay = 0): void
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
