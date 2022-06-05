<?php
namespace Orolyn\Net;

use function Orolyn\Lang\Int32;
use function Orolyn\Lang\String;

final class DnsEndPoint extends EndPoint
{
    private $host;

    private $port;

    public function __construct(string $host, int $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function __toString(): string
    {
        return String('%s:%s')->format($this->host, $this->port);
    }

    /**
     * @param DnsEndPoint $endPoint
     * @return bool
     */
    public function equals(DnsEndPoint $endPoint): bool
    {
        return $endPoint->host === $this->host && $endPoint->port === $this->port;
    }
}
