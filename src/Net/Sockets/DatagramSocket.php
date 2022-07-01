<?php
namespace Orolyn\Net\Sockets;

use Orolyn\ArgumentOutOfRangeException;
use Orolyn\Collection\Dictionary;
use Orolyn\InvalidOperationException;
use Orolyn\Net\DnsEndPoint;
use Orolyn\Net\DnsResolver;
use Orolyn\Net\EndPoint;
use Orolyn\Net\IPAddress;
use Orolyn\Net\IPEndPoint;
use Orolyn\Net\IPHostEntry;
use Orolyn\NotImplementedException;
use function Orolyn\Suspend;

class DatagramSocket
{
    /**
     * @var int
     */
    private $packetSize;

    /**
     * @var resource
     */
    private $handle;

    /**
     * @var IPEndPoint
     */
    private $endPoint;

    /**
     * DatagramSocket constructor.
     * @param int $packetSize
     */
    public function __construct(int $packetSize = 512)
    {
        $this->packetSize = $packetSize;
        $this->endPoints = new Dictionary();
    }

    public function bind(): void
    {
        $this->init();
    }

    public function connect(EndPoint $endPoint): void
    {
        if ($endPoint instanceof DnsEndPoint) {
            if (null === $ipHostEntry = DnsResolver::lookup($endPoint->getHost())) {
                throw new \Exception('Could not resolve host name');
            }

            $this->endPoint = new IPEndPoint($ipHostEntry->getAddressList()[0], $endPoint->getPort());
        }

        if (!$endPoint instanceof IPEndPoint) {
            throw new NotImplementedException('Unknown endpoint type');
        }

        $this->endPoint = $endPoint;
    }

    public function send(DatagramPacket $packet): void
    {
        $endPoint = $packet->getEndPoint() ?? $this->endPoint;

        if (null === $endPoint) {
            throw new InvalidOperationException('Neither the socket nor packet has an assigned endpoint');
        }

        if ($endPoint instanceof DnsEndPoint) {
            if (null === $ipHostEntry = DnsResolver::lookup($endPoint->getHost())) {
                throw new \Exception('Could not resolve host name');
            }

            $endPoint = new IPEndPoint($ipHostEntry->getAddressList()[0], $endPoint->getPort());
        }

        if (!$endPoint instanceof IPEndPoint) {
            throw new NotImplementedException('Unknown endpoint type');
        }

        $this->init();

        for (;;) {
            $r = null;
            $w = [$this->handle];
            $e = null;

            if (0 === socket_select($r, $w, $e, 0)) {
                Suspend();
            } else {
                break;
            }
        }

        $packet->setPosition(0);

        socket_sendto(
            $this->handle,
            $packet->read($packet->getLength()),
            $this->packetSize,
            0,
            $endPoint->getAddress(),
            $endPoint->getPort()
        );
    }

    /**
     * Receive a packet.
     * Returns null if the socket is closed during the receive process.
     *
     * @return DatagramPacket|null
     * @throws SocketException
     * @throws ArgumentOutOfRangeException
     */
    public function recv(): ?DatagramPacket
    {
        $this->init();
        
        for (;;) {
            if (null === $this->handle) {
                return null;
            }

            $r = [$this->handle];
            $w = null;
            $e = null;

            $select = socket_select($r, $w, $e, 0);

            if (false === $select) {
                $this->throwLastError();
            } elseif ($select > 0) {
                if (false === socket_recvfrom($this->handle, $bytes, $this->packetSize, MSG_DONTWAIT, $name, $port)) {
                    $this->throwLastError();
                }

                $endpoint = new IPEndPoint(IPAddress::parse($name), (int)$port);

                if (!$this->endPoint || $this->endPoint->equals($endpoint)) {
                    $packet = new DatagramPacket($endpoint);
                    $packet->write($bytes);
                    $packet->setPosition(0);

                    return $packet;
                }
            }

            Suspend();
        }
    }

    public function close(): void
    {
        if (null !== $this->handle) {
            socket_close($this->handle);
            $this->handle = null;
        }
    }

    private function init(): void
    {
        if (null !== $this->handle) {
            return;
        }

        $this->handle = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_set_nonblock($this->handle);
    }

    /**
     * @throws SocketException
     */
    private function throwLastError(): void
    {
        throw new SocketException(socket_strerror(socket_last_error($this->handle)));
    }
}
