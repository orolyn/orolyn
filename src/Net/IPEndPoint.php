<?php
namespace Orolyn\Net;

use function Orolyn\Int32;
use function Orolyn\String;

final class IPEndPoint extends EndPoint implements ServerEndPoint
{
    /**
     * @var IPAddress
     */
    private $address;

    /**
     * @var int
     */
    private $port;

    /**
     * IPEndPoint constructor.
     * @param IPAddress $address
     * @param int $port
     */
    public function __construct(IPAddress $address, int $port)
    {
        $this->address = $address;
        $this->port = $port;
    }

    public static function parse(string $value): IPEndPoint
    {
        list($address, $port) = explode(':', $value);

        return new IPEndPoint(IPAddress::parse($address), (int)$port);
    }

    /**
     * @return IPAddress
     */
    public function getAddress(): IPAddress
    {
        return $this->address;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    public function __toString(): string
    {
        return sprintf('%s:%s', $this->address, $this->port);
    }

    /**
     * @param IpEndPoint $endPoint
     * @return bool
     */
    public function equals(IpEndPoint $endPoint): bool
    {
        return $endPoint->address->equals($endPoint->address) && $endPoint->port === $this->port;
    }
}
