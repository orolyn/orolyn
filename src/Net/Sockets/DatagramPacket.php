<?php
namespace Orolyn\Net\Sockets;

use Orolyn\IO\ByteArray;
use Orolyn\IO\ByteStream;
use Orolyn\Net\EndPoint;
use function Orolyn\UnsignedInt16;

class DatagramPacket extends ByteStream
{
    private $endPoint;

    public function __construct(EndPoint $endPoint = null)
    {
        parent::__construct('');

        $this->endPoint = $endPoint;
    }

    public function getEndPoint(): ?EndPoint
    {
        return $this->endPoint;
    }
}
