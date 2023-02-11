<?php

namespace Orolyn\Data\Mysql\Protocol;

use Orolyn\Endian;
use Orolyn\IO\ByteStream;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;

class Packet
{
    public function __construct(
        public readonly int $sequence,
        public readonly ByteStream $payload,
        public readonly int $length,
    ) {
    }

    public static function decode(IInputStream $stream): Packet
    {
        $length = $stream->readUnsignedInt24();
        $sequence = $stream->readUnsignedInt8();

        return new Packet($sequence, self::createPayload($stream->read($length)), $length);
    }

    public function encode(IOutputStream $stream): void
    {
        $stream->writeUnsignedInt24($this->payload->getLength());
        $stream->writeUnsignedInt8($this->sequence);
        $this->payload->reset();
        $stream->write($this->payload);
    }

    public static function createPayload(string $data = ''): ByteStream
    {
        $payload = new ByteStream($data);
        $payload->setEndian(Endian::LittleEndian);

        return $payload;
    }

    public function __toString(): string
    {
        $stream = new ByteStream();
        $this->encode($stream);

        return (string)$stream;
    }
}
