<?php

namespace Orolyn\Data\Mysql\Protocol\Session;

use Orolyn\Data\Mysql\Protocol\LengthEncoded;
use Orolyn\IO\ByteStream;

class SessionTrackStateChange
{
    public function __construct(
        public readonly string $data
    ) {
    }

    public static function decode(ByteStream $stream): SessionTrackStateChange
    {
        return new SessionTrackStateChange(LengthEncoded::decodeLengthEncodedString($stream));
    }
}
