<?php

namespace Orolyn\Concurrency;

use Orolyn\AggregateException;
use Orolyn\EventArgs;

class UnobservedTaskExceptionEventArgs extends EventArgs
{
    private AggregateException $exception;
    private bool $observed = false;

    /**
     * @param AggregateException $exception
     */
    public function __construct(AggregateException $exception)
    {
        $this->exception = $exception;
    }

    /**
     * @return AggregateException
     */
    public function getException(): AggregateException
    {
        return $this->exception;
    }

    /**
     * @return bool
     */
    public function isObserved(): bool
    {
        return $this->observed;
    }

    /**
     * @return void
     */
    public function setObserved(): void
    {
        $this->observed = true;
    }
}
