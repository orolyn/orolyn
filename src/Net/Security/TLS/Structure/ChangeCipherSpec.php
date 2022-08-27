<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\ArgumentException;
use Orolyn\ArgumentOutOfRangeException;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;

class ChangeCipherSpec extends Structure
{
    /**
     * @inheritdoc
     */
    public function encode(IOutputStream $stream): void
    {
        $stream->writeUnsignedInt8(1);
    }

    /**
     * @inheritdoc
     */
    public static function decode(IInputStream $stream, ?bool $server = null): static
    {
        $stream->readUnsignedInt8();

        return new ChangeCipherSpec();
    }
}
