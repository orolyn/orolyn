<?php

namespace Orolyn\Serialization;

use Closure;
use Orolyn\Reflection;
use ReflectionException;

class SerializationContext
{
    public function __construct(mixed ...$properties)
    {
        $reflection = Reflection::getReflectionClass($this);

        foreach ($properties as $name => $value) {
            try {
                $reflectionProperty = $reflection->getProperty($name);
                $reflectionProperty->setValue($this, $value);
            } catch (ReflectionException $exception) {
            }
        }
    }

    public function __get(string $name): mixed
    {
        return null;
    }
}
