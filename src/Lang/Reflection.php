<?php
namespace Orolyn\Lang;

function ClassInstanceOf(string $className, string $inheritedClassName): bool
{
    return
        in_array($inheritedClassName, class_parents($className)) ||
        in_array($inheritedClassName, class_implements($className));
}

function ClassExists(string $className): bool
{
    return class_exists($className);
}