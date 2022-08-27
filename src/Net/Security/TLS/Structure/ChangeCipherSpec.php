<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\Net\Security\TLS\Context;
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
    public static function decode(IInputStream $stream, ?Context $context = null): static
    {
        $stream->readUnsignedInt8();

        return new ChangeCipherSpec();
    }
}
