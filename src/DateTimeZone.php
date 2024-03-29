<?php
namespace Orolyn;

use DateTimeZone as BaseDateTimeZone;
use Orolyn\Collection\Dictionary;
use Orolyn\Collection\IDictionary;

class DateTimeZone
{
    public const AFRICA = 1;
    public const AMERICA = 2;
    public const ANTARCTICA = 4;
    public const ARCTIC = 8;
    public const ASIA = 16;
    public const ATLANTIC = 32;
    public const AUSTRALIA = 64;
    public const EUROPE = 128;
    public const INDIAN = 256;
    public const PACIFIC = 512;
    public const UTC = 1024;
    public const ALL = 2047;
    public const ALL_WITH_BC = 4095;
    public const PER_COUNTRY = 4096;

    /**
     * @var BaseDateTimeZone
     */
    private $internal;

    public function __construct($timezone)
    {
        $this->internal = new BaseDateTimeZone($timezone);
    }

    public function __internal()
    {
        return $this->internal;
    }

    public function getName(): string
    {
        return $this->internal->getName();
    }

    public function getLocation(): IDictionary
    {
        $location = new Dictionary();

        foreach ($this->internal->getLocation() as $key => $value) {
            $location->add($key, $value);
        }

        return $location;
    }

    public function getOffset(DateTime $datetime): int
    {
        return $this->internal->getOffset($datetime->__internal());
    }

    public function getTransitions($timestamp_begin = null, $timestamp_end = null)
    {
        BaseDateTimeZone::getTransitions($timestamp_begin, $timestamp_end); // TODO: Change the autogenerated stub
    }

    public static function listAbbreviations()
    {
        BaseDateTimeZone::listAbbreviations(); // TODO: Change the autogenerated stub
    }

    public static function listIdentifiers($what = BaseDateTimeZone::ALL, $country = null)
    {
        BaseDateTimeZone::listIdentifiers($what, $country); // TODO: Change the autogenerated stub
    }
}
