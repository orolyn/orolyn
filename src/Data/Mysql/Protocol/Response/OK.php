<?php

namespace Orolyn\Data\Mysql\Protocol\Response;

use Orolyn\Data\Mysql\Protocol\Capability;
use Orolyn\Data\Mysql\Protocol\LengthEncoded;
use Orolyn\Data\Mysql\Protocol\ServerStatusFlag;
use Orolyn\Data\Mysql\Protocol\Session\SessionStateInfo;
use Orolyn\IO\ByteStream;

class OK
{
    public function __construct(
        public readonly int $tag,
        public readonly int $affectedRows,
        public readonly int $lastInsertId,
        public readonly int $serverStatus,
        public readonly int $warningCount,
        public readonly ?string $info,
        public readonly ?string $sessionStateInfo,
    ) {
    }

    public static function decode(ByteStream $stream, int $capabilities): OK
    {
        $tag = $stream->readUnsignedInt8();
        $affectedRows = LengthEncoded::decodeLengthEncodedInteger($stream);
        $lastInsertId = LengthEncoded::decodeLengthEncodedInteger($stream);
        $serverStatus = $stream->readUnsignedInt16();
        $warningCount = $stream->readUnsignedInt16();

        $info = null;
        $sessionStateInfo = null;

        if ($stream->getBytesAvailable() > 0) {
            $info = LengthEncoded::decodeLengthEncodedstring($stream);

            if (
                $stream->getBytesAvailable() > 0 &&
                $serverStatus & ServerStatusFlag::SERVER_SESSION_STATE_CHANGED &&
                $capabilities & Capability::CLIENT_SESSION_TRACK
            ) {
                $sessionStateInfo = SessionStateInfo::decode($stream);
            }
        }

        return new OK($tag, $affectedRows, $lastInsertId, $serverStatus, $warningCount, $info, $sessionStateInfo);
    }
}
