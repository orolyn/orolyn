<?php
namespace Orolyn;

use DateInterval as BaseDateInterval;

class DateInterval
{
    public $y;
    public $m;
    public $d;
    public $h;
    public $i;
    public $s;
    public $f;
    public $invert;
    public $days;

    private $internal;

    public function __construct(string $intervalSpec)
    {
        $this->internal = new BaseDateInterval($intervalSpec);

        self::bindProperties($this->internal, $this);
    }

    public function __internal()
    {
        return $this->internal;
    }

    public function format(string $format): string
    {
        return $this->internal->format($format);
    }

    public static function createFromDateString(string $time): DateInterval
    {
        $base = BaseDateInterval::createFromDateString($time);
        $interval = new DateInterval('P1S');

        self::bindProperties($base, $interval);

        return $interval;
    }

    private static function bindProperties(BaseDateInterval $base, DateInterval $extended): void
    {
        $extended->y = &$base->y;
        $extended->m = &$base->m;
        $extended->d = &$base->d;
        $extended->h = &$base->h;
        $extended->i = &$base->i;
        $extended->s = &$base->s;
        $extended->f = &$base->f;
        $extended->invert = &$base->invert;
        $extended->days = &$base->days;
    }
}
