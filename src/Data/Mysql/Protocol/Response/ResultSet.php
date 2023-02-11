<?php

namespace Orolyn\Data\Mysql\Protocol\Response;

use Orolyn\Data\Mysql\MysqlHandle;
use Orolyn\Data\Mysql\Protocol\Capability;
use Orolyn\Data\Mysql\Protocol\LengthEncoded;
use Orolyn\IO\ByteStream;

class ResultSet
{
    public static function decode(ByteStream $stream, int $capabilities, MysqlHandle $handle): ResultSet
    {
        $columnCount = LengthEncoded::decodeLengthEncodedInteger($stream);
        $sendMetadata = false;
        $columnDefinitions = [];

        if ($capabilities & Capability::MARIADB_CLIENT_CACHE_METADATA) {
            $sendMetadata = $stream->readBool();
        }

        if (!($capabilities & Capability::MARIADB_CLIENT_CACHE_METADATA) || $sendMetadata) {
            for ($i = 0; $i < $columnCount; $i++) {
                $columnDefinitions[] = ColumnDefinition::decode($handle->getPacket()->payload, $capabilities);
            }
        }

        var_dump($columnDefinitions);

        die();
    }
}
