<?php

namespace Orolyn;

use Closure;
use ReflectionFunction;
use WeakReference;

class WeakClosure
{
    private WeakReference $reference;
    private Closure $closure;

    public function __construct(callable $callback)
    {
        $reflection = new ReflectionFunction($callback);

        if (null === $object = $reflection->getClosureThis()) {
            throw new \InvalidArgumentException('The callback must have a class scope');
        }

        $this->reference = WeakReference::create($object);
        $this->closure = Closure::bind($callback, $this);
    }

    public function hasObject(): bool
    {
        return null !== $this->reference->get();
    }

    public function __invoke(mixed ...$args)
    {
        if (null === $object = $this->reference->get()) {
            throw new \RuntimeException('The object referenced in this weak closure has been destroyed');
        }

        $this->closure->call($object, ...$args);
    }
}