<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\ByteConverter;
use Orolyn\Net\Security\TLS\Context;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;

/**
 * uint16 ProtocolVersion;
 */
enum ProtocolVersion: int implements IStructure
{
    case Version13 = 0x0304;
    case Version12 = 0x0303;
    case Version11 = 0x0302;
    case Version10 = 0x0301;

    /**
     * @inheritdoc
     */
    public function encode(IOutputStream $stream): void
    {
        $stream->writeUnsignedInt16($this->value);
    }

    /**
     * @inheritdoc
     */
    public static function decode(IInputStream $stream, ?Context $context = null): static
    {
        return ProtocolVersion::from($stream->readUnsignedInt16());
    }

    /**
     * @return string
     */
    public function toBytes(): string
    {
        return ByteConverter::getBinaryUnsignedInt16($this->value);
    }
}
