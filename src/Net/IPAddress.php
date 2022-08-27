<?php
namespace Orolyn\Net;

use Orolyn\ArgumentException;
use Orolyn\ByteConverter;
use Orolyn\Endian;
use Orolyn\FormatException;
use Orolyn\IEquatable;
use Orolyn\NotImplementedException;
use ReflectionClass;
use ReflectionException;

final class IPAddress implements IEquatable
{
    private string $address;

    public function __construct(int|string $address)
    {
        if (is_int($address)) {
            $address = ByteConverter::getBinaryInt32($address, Endian::BigEndian);
        }

        if (false === inet_ntop($address)) {
            throw new ArgumentException('$address is a bad IP address');
        }

        $this->address = $address;
    }

    /**
     * @param string $ipString
     * @return IPAddress|null
     */
    public static function parse(string $ipString): null|IPAddress
    {
        if (false === $address = inet_pton($ipString)) {
            return null;
        }

        try {
            /** @var IPAddress $ipAddress */
            $ipAddress = (new ReflectionClass(IPAddress::class))->newInstanceWithoutConstructor();
            $ipAddress->address = $address;
        } catch (ReflectionException $exception) {
            // N/A
        }

        return $ipAddress;
    }

    public function equals($value): bool
    {
        return $value instanceof IPAddress && $value->address === $this->address;
    }

    public function getHashCode(): int
    {
        if (4 === strlen($this->address)) {
            return ByteConverter::getInt64($this->address);
        }

        $a = ByteConverter::getInt64(substr($this->address, 0, 8));
        $b = ByteConverter::getInt64(substr($this->address, 8, 16));

        return $a ^ $b;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return inet_ntop($this->address);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
