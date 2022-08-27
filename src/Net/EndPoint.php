<?php
namespace Orolyn\Net;

use Orolyn\IEquatable;

abstract class EndPoint
{
    /**
     * @param string|IPAddress $host
     * @param int $port
     * @return IPEndPoint|DnsEndPoint
     */
    public static function create(string|IPAddress $host, int $port): IPEndPoint|DnsEndPoint
    {
        if ($host instanceof IPAddress) {
            return new IPEndPoint($host, $port);
        }

        if ($ipAddress = IPAddress::parse($host)) {
            return new IPEndPoint($ipAddress, $port);
        }

        return new DnsEndPoint($host, $port);
    }
}
