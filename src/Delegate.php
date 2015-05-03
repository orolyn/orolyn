<?php
namespace Orolyn;

class Delegate
{
    private $object;

    private $method;

    /**
     * @var callable
     */
    private $closure;

    public function __construct(?object $object, $method)
    {
        $this->object = $object;
        $this->method = $method;
    }

    public function call(...$args)
    {
        if (null === $this->closure) {
            if (null === $this->object) {
                $this->closure = $this->method;
            } else {
                $reflectionClass = new \ReflectionClass($this->object);
                $reflectionMethod = $reflectionClass->getMethod($this->method);
                $this->closure = $reflectionMethod->getClosure($this->object);
            }
        }

        return call_user_func($this->closure, ...$args);
    }
}
