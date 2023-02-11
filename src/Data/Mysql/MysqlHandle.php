<?php

namespace Orolyn\Data\Mysql;

use Orolyn\Data\DriverException;
use Orolyn\Data\Mysql\Protocol\Command\Command;
use Orolyn\Data\Mysql\Protocol\Packet;
use Orolyn\IO\ByteStream;
use Orolyn\Net\EndPoint;
use Orolyn\Net\Sockets\Socket2;

class MysqlHandle
{
    private Socket2 $socket;
    private int $sequence = 0;

    public function __construct(
        EndPoint $endPoint,
    ) {
        $this->socket = new Socket2();
        $this->socket->connect($endPoint);
    }

    public function getPacket(): Packet
    {
        $packet = Packet::decode($this->socket);

        if ($this->sequence++ !== $packet->sequence) {
            throw new DriverException('Server packet number out of sequence');
        }

        return $packet;
    }

    public function sendCommand(Command $command)
    {
        $stream = Packet::createPayload();
        $stream->writeUnsignedInt8($command->getHeader());
        $command->getPayload($stream);

        $this->sendPacket($stream);
    }

    public function sendPacket(ByteStream|string $stream): void
    {
        if (is_string($stream)) {
            $stream = new ByteStream($stream);
        }

        $packet = new Packet($this->sequence++, $stream, $stream->getLength());
        $packet->encode($this->socket);
        $this->socket->flush();
    }

    public function resetSequence(): void
    {
        $this->sequence = 0;
    }
}
