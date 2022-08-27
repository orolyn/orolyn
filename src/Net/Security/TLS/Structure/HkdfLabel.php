<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;

/**
 * struct {
 *     uint16 length = Length;
 *     opaque label<7..255> = "tls13 " + Label;
 *     opaque context<0..255> = Context;
 * } HkdfLabel;
 */
class HkdfLabel extends Structure
{
    public function __construct(
        public readonly int $length,
        public readonly HkdfLabelType $type,
        public readonly string $context = ''
    ) {
    }

    /**
     * @inheritdoc
     */
    public function encode(IOutputStream $stream): void
    {
        $stream->writeUnsignedInt16($this->length);
        $stream->writeUnsignedInt8(strlen($this->type->value));
        $stream->write($this->type->value);
        $stream->writeUnsignedInt8(strlen($this->context));
        $stream->write($this->context);
    }

    /**
     * @inheritdoc
     */
    public static function decode(IInputStream $stream, ?bool $server = null): static
    {
        return new HkdfLabel(
            $stream->readUnsignedInt16(),
            HkdfLabelType::from($stream->read($stream->readUnsignedInt8())),
            $stream->read($stream->readUnsignedInt8())
        );
    }
}
