<?php
namespace Orolyn;

class ArgumentException extends RuntimeException
{
    public static function assertInstanceOf(string $name, $value, string $className): void
    {
        if (!$value instanceof $className) {
            throw new static(sprintf('Argument "%s" must be instance of %s', $name, $className));
        }
    }
}
