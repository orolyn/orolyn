<?php
namespace Orolyn\Lang;

use Orolyn\Collection\Dictionary;

/**
 * @internal
 */
class InternalCaller
{
    private static Dictionary $classes;
    private static Dictionary $methods;

    public static function callStaticMethod(string $class, string $method, ...$args)
    {
        $reflectionMethod = self::getReflectionMethod($class, $method);
        return $reflectionMethod->invoke(null, ...$args);
    }

    public static function callMethod(object $object, string $method, ...$args)
    {
        $reflectionMethod = self::getReflectionMethod(get_class($object), $method);
        return $reflectionMethod->invoke($object, ...$args);
    }

    private static function getReflectionClass(string $class): \ReflectionClass
    {
        if (!class_exists($class)) {
            throw new \InvalidArgumentException("Class \"$class\" does not exist");
        }

        self::$classes = self::$classes ?? new Dictionary();

        /** @var \ReflectionClass $reflectionClass */
        if (!self::$classes->try($class, $reflectionClass)) {
            $reflectionClass = new \ReflectionClass($class);
            self::$classes->add($class, $reflectionClass);
        }

        return $reflectionClass;
    }

    private static function getReflectionMethod(string $class, string $method): \ReflectionMethod
    {
        if (!method_exists($class, $method)) {
            throw new \InvalidArgumentException("Method \"$class::$method\" does not exist");
        }

        self::$methods = self::$methods ?? new Dictionary();

        $name = "$class::$method";

        /** @var \ReflectionMethod $reflectionMethod */
        if (!self::$methods->try($name, $reflectionMethod)) {
            $reflectionMethod = new \ReflectionMethod($class, $method);
            self::$methods->add($name, $reflectionMethod);
            $reflectionMethod->setAccessible(true);
        }

        return $reflectionMethod;
    }
}
