<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\IO\ByteStream;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;

/**
 * struct {
 *     ExtensionType extension_type;
 *     opaque extension_data<0..2^16-1>;
 * } Extension;
 */
class Extension extends Structure
{
    public function __construct(
        public readonly ExtensionType $extensionType,
        public readonly IStructure $extensionData
    ) {
    }

    /**
     * @inheritdoc
     */
    public function encode(IOutputStream $stream): void
    {
        $this->extensionType->encode($stream);

        $byteStream = self::createByteStream($this->extensionData);

        $stream->writeUnsignedInt16($byteStream->getLength());
        $stream->write($byteStream);
    }

    /**
     * @inheritdoc
     */
    public static function decode(IInputStream $stream, ?bool $server = null): static
    {
        $type = ExtensionType::decode($stream);
        $byteStream = self::createByteStream($stream->read($stream->readUnsignedInt16()));

        if ($server) {
            $data = match ($type) {
                ExtensionType::SupportedVersions => ProtocolVersionVector::decode($byteStream),
                ExtensionType::KeyShare => KeyShareEntryVector::decode($byteStream)
            };
        } else {
            $data = match ($type) {
                ExtensionType::KeyShare => KeyShareEntry::decode($byteStream),
                ExtensionType::SupportedVersions => ProtocolVersion::decode($byteStream)
            };
        }

        return new Extension($type, $data);
    }
}
