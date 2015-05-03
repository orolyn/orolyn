<?php
namespace Orolyn\Net\Sockets\Options;

use Orolyn\Collection\ICollection;
use Orolyn\Net\IPEndPoint;
use Orolyn\StandardObject;
use Traversable;

class SocketOptions extends Options
{
    public function getBindTo(): IPEndPoint
    {
        return IPEndPoint::parse($this->get('bindto'));
    }

    public function setBindTo(IPEndPoint $endPoint): static
    {
        return $this->set('bindto', $endPoint->__toString());
    }

    public function getTcpNodelay(): bool
    {
        return $this->get('tcp_nodelay');
    }

    public function setTcpNodelay(?bool $tcpNodelay): static
    {
        return $this->set('tcp_nodelay', $tcpNodelay);
    }
}
