<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\BitConverter;
use Orolyn\Net\Security\TLS\Context;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;

/**
 * enum {
 *     change_cipher_spec(20), alert(21), handshake(22),
 *     application_data(23), (255)
 * } ContentType;
 */
enum ContentType: int implements IStructure
{
    case ChangeCipherSpec = 20;
    case Alert = 21;
    case Handshake = 22;
    case ApplicationData = 23;

    /**
     * @inheritdoc
     */
    public function encode(IOutputStream $stream): void
    {
        $stream->writeUnsignedInt8($this->value);
    }

    /**
     * @return string
     */
    public function toByte(): string
    {
        return BitConverter::getBinaryUnsignedInt8($this->value);
    }

    /**
     * @inheritdoc
     */
    public static function decode(IInputStream $stream, ?Context $context = null): static
    {
        return ContentType::from($stream->readUnsignedInt8());
    }

    /**
     * @param string $byte
     * @return ContentType
     */
    public static function fromByte(string $byte): ContentType
    {
        return self::from(ord($byte));
    }
}
