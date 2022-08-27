<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\Net\Security\TLS\Context;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;
use Orolyn\SecureRandom;
use OutOfRangeException;

/**
 * opaque SessionID<0..32>;
 */
class SessionId extends Structure
{
    public function __construct(
        public readonly string $bytes = ''
    ) {
        if (strlen($this->bytes) > 32) {
            throw new OutOfRangeException('Session ID cannot be greater than 32 in length');
        }
    }

    /**
     * @inheritdoc
     */
    public function encode(IOutputStream $stream): void
    {
        $stream->writeUnsignedInt8(strlen($this->bytes));
        $stream->write($this->bytes);
    }

    /**
     * @inheritdoc
     */
    public static function decode(IInputStream $stream, ?Context $context = null): static
    {
        return new SessionId($stream->read($stream->readUnsignedInt8()));
    }

    /**
     * @return SessionId
     */
    public static function generate(): SessionId
    {
        return new SessionId(SecureRandom::generateBytes(32));
    }
}
