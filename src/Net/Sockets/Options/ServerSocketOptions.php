<?php
namespace Orolyn\Net\Sockets\Options;

class ServerSocketOptions extends Options
{
    public function getBacklog(): ?int
    {
        return $this->get('backlog');
    }

    public function setBacklog(?bool $backlog): static
    {
        return $this->set('backlog', $backlog);
    }

    public function getIPv6Only(): ?bool
    {
        return $this->get('ipv6_v6only');
    }

    public function setIPv6Only(?bool $ipv6Only): static
    {
        return $this->set('ipv6_v6only', $ipv6Only);
    }

    public function getSoReusePort(): ?bool
    {
        return $this->get('so_reuseport');
    }

    public function setSoReusePort(?bool $soReusePort): static
    {
        return $this->set('so_reuseport', $soReusePort);
    }

    public function getSoBroadcast(): ?bool
    {
        return $this->get('so_broadcast');
    }

    public function setSoBroadcast(?bool $soBroadcast): static
    {
        return $this->set('so_broadcast', $soBroadcast);
    }
}
