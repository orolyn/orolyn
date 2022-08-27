<?php
namespace Orolyn;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class Reflection
{
    /**
     * @param string|object $value
     * @return ReflectionClass
     * @throws ReflectionException
     */
    public static function getReflectionClass(string|object $value): ReflectionClass
    {
        static $reflections;

        $reflections = $reflections ?? [];
        $class = is_string($value) ? $value : get_class($value);

        if (array_key_exists($class, $reflections)) {
            return $reflections[$class];
        }

        return $reflections[$class] = new ReflectionClass($class);
    }

    /**
     * @param string|object $class
     * @param string $method
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    public static function getReflectionMethod(string|object $class, string $method): ReflectionMethod
    {
        static $reflections;

        $reflections = $reflections ?? [];
        $class = is_string($class) ? $class : get_class($class);
        $classMethod = $class . '::' . $method;

        if (array_key_exists($classMethod, $reflections)) {
            return $reflections[$classMethod];
        }

        return $reflections[$classMethod] = new ReflectionMethod($class, $method);
    }

    public static function classInstanceOf(string $className, string $inheritedClassName): bool
    {
        return
            in_array($inheritedClassName, class_parents($className)) ||
            in_array($inheritedClassName, class_implements($className));
    }

    /**
     * @param object $reflection
     * @param string $name
     * @return ReflectionAttribute[]
     */
    public static function getAttributes(object $reflection, string $name): array
    {
        if (!method_exists($reflection, 'getAttributes')) {
            throw new ArgumentException('Not a reflection type object');
        }

        return $reflection->getAttributes($name);
    }

    /**
     * @param object $reflection
     * @param string $name
     * @return ReflectionAttribute|null
     */
    public static function getSingleAttribute(object $reflection, string $name): ?ReflectionAttribute
    {
        $attributes = self::getAttributes($reflection, $name);

        if (empty($attributes)) {
            return null;
        }

        return $attributes[0];
    }
}

