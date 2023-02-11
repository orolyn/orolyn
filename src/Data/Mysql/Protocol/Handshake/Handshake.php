<?php

namespace Orolyn\Data\Mysql\Protocol\Handshake;

use Orolyn\Data\DriverException;
use Orolyn\Data\Mysql\Protocol\Capability;
use Orolyn\Data\Mysql\Protocol\CharacterSet;
use Orolyn\Data\Mysql\Protocol\Packet;
use Orolyn\Data\Mysql\Protocol\ProtocolVersion;
use Orolyn\FormatException;
use Orolyn\Math;
use Orolyn\Version;

class Handshake
{
    public function __construct(
        public readonly ProtocolVersion $protocolVersion,
        public readonly Version $serverVersion,
        public readonly int $threadId,
        public readonly int $capabilities,
        public readonly CharacterSet $characterSet,
        public readonly int $statusFlags,
        public readonly ?string $authPluginData = null,
        public readonly ?string $authPluginName = null
    ) {
    }

    public static function decode(Packet $packet): Handshake
    {
        $stream = $packet->payload;

        if (null === $protocolVersion = ProtocolVersion::tryFrom($stream->readUnsignedInt8())) {
            throw new DriverException('Unsupported protocol version');
        }

        try {
            $serverVersion = Version::parse($stream->readNullTerminated());
        } catch (FormatException $exception) {
            throw new DriverException('Invalid server version', 0, $exception);
        }

        $threadId = $stream->readUnsignedInt32();
        $authPluginData = $stream->read(8);
        $stream->read(); // reserved
        $capabilities = $stream->readUnsignedInt16();

        if (null === $characterSet = CharacterSet::getFromId($stream->readUnsignedInt8())) {
            throw new DriverException('Server provided invalid character set');
        }

        $statusFlags = $stream->readUnsignedInt16();
        $capabilities |= $stream->readUnsignedInt16() << 16;
        $authPluginName = null;
        $authPluginDataLen = 0;

        if ($capabilities & Capability::CLIENT_PLUGIN_AUTH) {
            $authPluginDataLen = $stream->readUnsignedInt8();
        } else {
            $stream->read();
        }

        $stream->read(6);

        if ($capabilities & Capability::CLIENT_MYSQL) {
            $stream->read(4);
        } else {
            $capabilities != $stream->readUnsignedInt16() << 32;
        }

        if ($capabilities & Capability::CLIENT_SECURE_CONNECTION) {
            $authPluginData .= $stream->read(Math::max(12, $authPluginDataLen - 9));
            $stream->read();
        }

        if ($capabilities & Capability::CLIENT_PLUGIN_AUTH) {
            $authPluginName = $stream->readNullTerminated();
        }

        return new Handshake(
            $protocolVersion,
            $serverVersion,
            $threadId,
            $capabilities,
            $characterSet,
            $statusFlags,
            $authPluginData,
            $authPluginName
        );
    }
}
