<?php

namespace Orolyn\Data\Mysql\Protocol\Session;

use Orolyn\Data\Mysql\Protocol\LengthEncoded;
use Orolyn\IO\ByteStream;

class SessionTrackSchema
{
    public function __construct(
        public readonly string $schema
    ) {
    }

    public static function decode(ByteStream $stream): SessionTrackSchema
    {
        return new SessionTrackSchema(LengthEncoded::decodeLengthEncodedString($stream));
    }
}
