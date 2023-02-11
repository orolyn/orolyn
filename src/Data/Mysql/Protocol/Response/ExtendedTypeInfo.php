<?php

namespace Orolyn\Data\Mysql\Protocol\Response;

use Orolyn\Data\DriverException;
use Orolyn\Data\Mysql\Protocol\LengthEncoded;
use Orolyn\IO\ByteStream;

class ExtendedTypeInfo
{
    public function __construct(
        public readonly ExtendedTypeInfoDataType $dataType,
        public readonly string $value
    ) {
    }

    public static function decode(ByteStream $stream): ExtendedTypeInfo
    {
        if (null === $dataType = ExtendedTypeInfoDataType::tryFrom($stream->readUnsignedInt8())) {
            throw new DriverException('Invalid extended type info data type');
        }

        $value = LengthEncoded::decodeLengthEncodedString($stream);

        return new ExtendedTypeInfo($dataType, $value);
    }
}
