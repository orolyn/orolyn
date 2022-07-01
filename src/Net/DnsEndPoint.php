<?php
namespace Orolyn\Net;

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
        return sprintf('%s:%s', $this->host, $this->port);
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
