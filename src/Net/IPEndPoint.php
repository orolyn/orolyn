<?php
namespace Orolyn\Net;

use function Orolyn\Lang\Int32;
use function Orolyn\Lang\String;

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
        return String('%s:%s')->format($this->address, $this->port);
    }
}
