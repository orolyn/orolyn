<?php
namespace Orolyn;

use function Orolyn\Lang\Suspend;

class Timer
{
    /**
     * @var float
     */
    private float $delay;

    /**
     * @var float
     */
    private float $target;

    /**
     * Creates a timer with the number of milliseconds delay and starts counting.
     *
     * @param float $delay
     * @throws ArgumentOutOfRangeException
     */
    public function __construct(float $delay)
    {
        if ($delay <= 0) {
            throw new ArgumentOutOfRangeException('delay');
        }

        $this->delay = $delay;
        $this->start();
    }

    /**
     * @param float $delay
     * @return Timer
     */
    public static function create(float $delay): Timer
    {
        return new Timer($delay);
    }

    /**
     * Sets the time to start counting to now.
     */
    public function start(): void
    {
        $this->target = microtime(true) + ($this->delay / 1000);
    }

    /**
     * Wait until the timer is complete. Suspends the current fiber if there is one.
     */
    public function wait(): void
    {
        while (!$this->isExpired()) {
            Suspend();
        }
    }

    public function getRemaining(): float
    {
        return ($this->target - microtime(true)) * 1000;
    }

    public function isExpired(): bool
    {
        return microtime(true) >= $this->target;
    }
}
