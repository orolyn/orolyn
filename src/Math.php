<?php
namespace Orolyn;

class Math
{
    public const PI = M_PI;
    public const RAD2DEG = 360 / (self::PI * 2);
    public const DEG2RAD = (self::PI * 2) / 360;

    public static function sqrt(float $v): float
    {
        return sqrt($v);
    }

    public static function max($a, $b)
    {
        return (float)$a === max((float)$a, (float)$b) ? $a : $b;
    }

    public static function min($a, $b)
    {
        return (float)$a === min((float)$a, (float)$b) ? $a : $b;
    }

    public static function floor(float $v): float
    {
        return floor($v);
    }

    public static function atan2(float $a, float $b)
    {
        return atan2($a, $b);
    }

    public static function asin(float $a)
    {
        return asin($a);
    }

    public static function abs(float $a): float
    {
        return abs($a);
    }

    public static function sign(float $a): int
    {
        return $a <=> 0;
    }

    public static function sin(float $a): float
    {
        return sin($a);
    }

    public static function cos(float $a): float
    {
        return cos($a);
    }

    public static function ceil(float $v): int
    {
        return ceil($v);
    }
}

