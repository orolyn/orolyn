<?php

namespace Orolyn\Concurrency;

abstract class Application
{
    abstract public function main(): void;

    public function terminate(): void
    {
    }

    public function onUnobservedTaskException(UnobservedTaskExceptionEventArgs $eventArgs): void
    {
        throw $eventArgs->getException()->flatten()->getExceptions()[0];
    }
}
