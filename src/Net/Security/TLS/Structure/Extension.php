<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\Net\Security\TLS\Context;
use Orolyn\ArgumentException;
use Orolyn\Collection\Dictionary;
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
    public readonly ProtocolVersionList $supportedVersionList;
    public readonly ProtocolVersion $supportedVersion;
    public readonly KeyShareEntryVector $keyShareEntryList;
    public readonly KeyShareEntry $keyShareEntry;
    public readonly ServerNameVector $serverNameList;
    public readonly SignatureSchemeVector $signatureSchemeList;

    public function __construct(
        public readonly ExtensionType $extensionType,
        public readonly IStructure $extensionData
    ) {
        if (ExtensionType::SupportedVersions === $this->extensionType) {
            $property = match ($this->extensionData::class) {
                ProtocolVersionList::class => 'supportedVersionList',
                ProtocolVersion::class => 'supportedVersion',
                KeyShareEntryVector::class => 'keyShareEntryList'
            };
        } elseif (ExtensionType::KeyShare === $this->extensionType) {
            $property = match ($this->extensionData::class) {
                KeyShareEntryVector::class => 'keyShareEntryList',
                KeyShareEntry::class => 'keyShareEntry'
            };
        } else {
            $property = match ($this->extensionData::class) {
                ServerNameVector::class => 'serverNameList',
                NamedGroupVector::class => 'namedGroupList',
                SignatureSchemeVector::class => 'signatureSchemeList'
            };
        }

        $this->{$property} = $extensionData;
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
    public static function decode(IInputStream $stream, ?Context $context = null): static
    {
        $type = ExtensionType::decode($stream);
        $byteStream = self::createByteStream($stream->read($stream->readUnsignedInt16()));

        /** @var class-string<IStructure> $class */
        $class = self::getStructureClass($type, $context->isServer);
        $data = $class::decode($byteStream, $context);

        return new Extension($type, $data);
    }

    /**
     * @param ExtensionType $extensionType
     * @return string
     */
    private static function getStructureClass(ExtensionType $extensionType, bool $server): string
    {
        static $mapServer;
        static $mapClient;

        if (null === $mapServer) {
            $mapServer = new Dictionary();
            $mapServer->add(ExtensionType::SupportedVersions, ProtocolVersion::class);
            $mapServer->add(ExtensionType::KeyShare, KeyShareEntry::class);
        }

        if (null === $mapClient) {
            $mapClient = new Dictionary();
            $mapClient->add(ExtensionType::SupportedVersions, ProtocolVersionList::class);
            $mapClient->add(ExtensionType::KeyShare, KeyShareEntryVector::class);
            $mapClient->add(ExtensionType::ServerName, ServerName::class);
            $mapClient->add(ExtensionType::SupportedGroups, NamedGroupVector::class);
        }

        return $server ? $mapClient->get($extensionType) : $mapServer->get($extensionType);
    }
}
