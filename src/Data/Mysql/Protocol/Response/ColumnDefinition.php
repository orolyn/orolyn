<?php

namespace Orolyn\Data\Mysql\Protocol\Response;

use Orolyn\Data\DriverException;
use Orolyn\Data\Mysql\Protocol\Capability;
use Orolyn\Data\Mysql\Protocol\CharacterSet;
use Orolyn\Data\Mysql\Protocol\FieldType;
use Orolyn\Data\Mysql\Protocol\LengthEncoded;
use Orolyn\IO\ByteStream;

class ColumnDefinition
{
    public function __construct(
        public readonly string $catalog,
        public readonly string $schema,
        public readonly string $tableAlias,
        public readonly string $table,
        public readonly string $columnAlias,
        public readonly string $column,
        public readonly array $extendedTypeInfo,
        public readonly int $lengthOfFixedFields,
        public readonly CharacterSet $characterSet,
        public readonly int $maxColumnSize,
        public readonly FieldType $fieldType,
        public readonly int $fieldDetailsFlag,
        public readonly int $decimals,
    ) {
    }

    public static function decode(ByteStream $stream, int $capabilities): ColumnDefinition
    {
        $catalog = LengthEncoded::decodeLengthEncodedString($stream);
        $schema = LengthEncoded::decodeLengthEncodedString($stream);
        $tableAlias = LengthEncoded::decodeLengthEncodedString($stream);
        $table = LengthEncoded::decodeLengthEncodedString($stream);
        $columnAlias = LengthEncoded::decodeLengthEncodedString($stream);
        $column = LengthEncoded::decodeLengthEncodedString($stream);
        $extendedTypeInfo = [];

        if ($capabilities & Capability::MARIADB_CLIENT_EXTENDED_TYPE_INFO) {
            $extendedTypeInfoStream = new ByteStream($stream->read(LengthEncoded::decodeLengthEncodedInteger($stream)));

            while ($extendedTypeInfoStream->getBytesAvailable() > 0) {
                $extendedTypeInfo[] = ExtendedTypeInfo::decode($extendedTypeInfoStream);
            }
        }

        $lengthOfFixedFields = LengthEncoded::decodeLengthEncodedInteger($stream);

        if (null === $characterSet = CharacterSet::getFromId($stream->readInt16())) {
            throw new DriverException('Invalid character set');
        }

        $maxColumnSize = $stream->readUnsignedInt32();

        if (null === $fieldType = FieldType::tryFrom($stream->readUnsignedInt8())) {
            throw new DriverException('Invalid field type');
        }

        $fieldDetailsFlag = $stream->readUnsignedInt16();
        $decimals = $stream->readUnsignedInt8();

        $stream->read(2); // unused

        return new ColumnDefinition(
            $catalog,
            $schema,
            $tableAlias,
            $table,
            $columnAlias,
            $column,
            $extendedTypeInfo,
            $lengthOfFixedFields,
            $characterSet,
            $maxColumnSize,
            $fieldType,
            $fieldDetailsFlag,
            $decimals,
        );
    }
}
