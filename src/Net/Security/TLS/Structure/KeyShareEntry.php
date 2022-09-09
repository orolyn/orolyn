<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\Net\Security\TLS\Context;
use Orolyn\ArgumentException;
use Orolyn\ArgumentOutOfRangeException;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;
use Orolyn\Net\Security\Structure\Version13\Random13;

/**
 * struct {
 *     NamedGroup group;
 *     opaque key_exchange<1..2^16-1>;
 * } KeyShareEntry;
 */
class KeyShareEntry extends Structure
{
    public function __construct(
        public readonly NamedGroup $group,
        public readonly string $keyExchange
    ) {
        $length = strlen($this->keyExchange);

        if ($length < 1 || $length > (2**16)-1) {
            throw new ArgumentOutOfRangeException('Key exchange must be between 1 and 2^16-1 bytes in length');
        }
    }

    /**
     * @inheritdoc
     */
    public function encode(IOutputStream $stream): void
    {
        $this->group->encode($stream);
        $stream->writeUnsignedInt16(strlen($this->keyExchange));
        $stream->write($this->keyExchange);
    }

    /**
     * @inheritdoc
     */
    public static function decode(IInputStream $stream, ?Context $context = null): static
    {
        return new KeyShareEntry(
            NamedGroup::decode($stream, $context),
            $stream->read($stream->readUnsignedInt16())
        );
    }
}
