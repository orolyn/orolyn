<?php

namespace Orolyn\Data\Mysql\Protocol\Session;

use Orolyn\Data\DriverException;
use Orolyn\Data\Mysql\Protocol\LengthEncoded;
use Orolyn\IO\ByteStream;

class SessionStateInfo
{
    public function __construct(
        public readonly SessionChangeType $sessionChangeType,
        public readonly ?int $totalLength,
        public readonly
            SessionTrackSchema|SessionTrackSystemVariables|SessionTrackStateChange|string $sessionDataChange,
    ) {
    }

    public static function decode(ByteStream $stream): SessionStateInfo
    {
        if (null === $sessionChangeType = SessionChangeType::tryFrom($stream->readUnsignedInt8())) {
            throw new DriverException('Unknown session change type');
        }

        $totalLength = null;

        if (SessionChangeType::SessionTrackStateChange !== $sessionChangeType) {
            $totalLength = LengthEncoded::decodeLengthEncodedInteger($stream);
        }

        $sessionDataChange = match ($sessionChangeType) {
            SessionChangeType::SessionTrackSystemVariables => SessionTrackSystemVariables::decode($stream),
            SessionChangeType::SessionTrackSchema => SessionTrackSchema::decode($stream),
            SessionChangeType::SessionTrackStateChange => SessionTrackStateChange::decode($stream),
            default => LengthEncoded::decodeLengthEncodedString($stream)
        };

        return new SessionStateInfo($sessionChangeType, $totalLength, $sessionDataChange);
    }
}
