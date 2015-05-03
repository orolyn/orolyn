<?php
namespace Orolyn;

use DateTime as BaseDateTime;
use Orolyn\Collection\Dictionary;
use Orolyn\Collection\IDictionary;

class DateTime
{
    const ATOM = 'Y-m-d\TH:i:sP';
    const COOKIE = 'l, d-M-y H:i:s T';
    const ISO8601 = 'Y-m-d\TH:i:sO';
    const RFC822 = 'D, d M y H:i:s O';
    const RFC850 = 'l, d-M-y H:i:s T';
    const RFC1036 = 'D, d M y H:i:s O';
    const RFC1123 = 'D, d M Y H:i:s O';
    const RFC2822 = 'D, d M Y H:i:s O';
    const RFC3339 = 'Y-m-d\TH:i:sP';
    const RFC3339_EXTENDED = 'Y-m-d\TH:i:s.vP';
    const RFC7231 = 'D, d M Y H:i:s \G\M\T';
    const RSS = 'D, d M Y H:i:s O';
    const W3C = 'Y-m-d\TH:i:sP';

    /**
     * @var BaseDateTime
     */
    private $internal;

    public function __construct(string $time = 'now', DateTimeZone $timezone = null)
    {
        $this->internal = new BaseDateTime($time, $timezone ? $timezone->__internal() : null);
    }

    public function __internal()
    {
        return $this->internal;
    }

    public function format($format)
    {
        return $this->internal->format($format);
    }

    public function modify($modify)
    {
        return $this->internal->modify($modify);
    }

    public function add(DateInterval $interval)
    {
        return $this->internal->add($interval->__internal());
    }

    public function sub(DateInterval $interval)
    {
        return $this->internal->sub($interval->__internal());
    }

    public function getTimezone(): DateTimeZone
    {
        return new DateTimeZone($this->internal->getTimezone()->getName());
    }

    public function setTimezone(DateTimeZone $timezone)
    {
        return $this->internal->setTimezone($timezone->__internal());
    }

    public function getOffset()
    {
        return $this->internal->getOffset();
    }

    public function setTime($hour, $minute, $second = 0, $microseconds = 0)
    {
        return $this->internal->setTime($hour, $minute, $second, $microseconds);
    }

    public function setDate($year, $month, $day)
    {
        return $this->internal->setDate($year, $month, $day);
    }

    public function setISODate($year, $week, $day = 1)
    {
        return $this->internal->setISODate($year, $week, $day);
    }

    public function setTimestamp(int $unixtimestamp)
    {
        return $this->internal->setTimestamp($unixtimestamp);
    }

    public function getTimestamp(): int
    {
        return $this->internal->getTimestamp();
    }

    public function diff(DateTime $datetime2, $absolute = false): ?DateInterval
    {
        if ($base = $this->internal->diff($datetime2->__internal(), $absolute)) {
            $interval = new DateInterval();
        }
    }

    public static function createFromFormat($format, $time, DateTimeZone $timezone = null): ?DateTime
    {
        if ($base = BaseDateTime::createFromFormat($format, $time, $timezone->__internal())) {
            $datetime = new static();
            $datetime->internal = $base;
        }

        return null;
    }

    public static function getLastErrors(): IDictionary
    {
        $errors = new Dictionary();

        foreach (BaseDateTime::getLastErrors() as $key => $value) {
            if (is_array($value)) {
                $sub = new Dictionary();

                foreach ($value as $subkey => $subvalue) {
                    $sub->add($subkey, $subvalue);
                }

                $value = $sub;
            }

            $errors->add($key, $value);
        }

        return $errors;
    }
}
