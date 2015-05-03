<?php
namespace Orolyn\Net;

use function Orolyn\Lang\Int32;
use function Orolyn\Lang\String;
use function Orolyn\Lang\UnsignedInt8;
use Orolyn\StandardObject;

final class IPAddress
{
    private $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }

    public static function parse(string $address): IPAddress
    {
        // TODO: extend this method

        $parts = String($address)->explode('.');

        $int = Int32(
            UnsignedInt8((int)$parts[3])->getBytes() .
            UnsignedInt8((int)$parts[2])->getBytes() .
            UnsignedInt8((int)$parts[1])->getBytes() .
            UnsignedInt8((int)$parts[0])->getBytes()
        );

        return new IPAddress($int->getValue());
    }

    public function equals($value): bool
    {
        return $value instanceof IPAddress && $value->value === $this->value;
    }

    public function getHashCode(): int
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return String('%s.%s.%s.%s')->format(
            UnsignedInt8($this->value >> 24),
            UnsignedInt8($this->value >> 16),
            UnsignedInt8($this->value >> 8),
            UnsignedInt8($this->value)
        );
    }
}
