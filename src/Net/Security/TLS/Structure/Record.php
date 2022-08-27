<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\Net\Security\TLS\Context;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;
use Orolyn\Net\Security\Structure\Version13\Random13;

/**
 * struct {
 *     ContentType type;
 *     ProtocolVersion legacy_record_version;
 *     uint16 length;
 *     opaque fragment[TLSPlaintext.length];
 * } TLSPlaintext;
 *
 * struct {
 *     ContentType opaque_type = application_data; // 23
 *     ProtocolVersion legacy_record_version = 0x0303; // TLS v1.2
 *     uint16 length;
 *     opaque encrypted_record[TLSCiphertext.length];
 * } TLSCiphertext;
 */
class Record extends Structure
{
    public readonly int $length;

    public function __construct(
        public readonly ContentType $contentType,
        public readonly string $bytes
    ) {
        $this->length = strlen($bytes);
    }

    public function getLength(): int
    {
        return strlen($this->bytes);
    }

    public function getHeader(): string
    {
        $stream = self::createByteStream();
        $this->contentType->encode($stream);
        ProtocolVersion::Version12->encode($stream);
        $stream->writeUnsignedInt16($this->length);

        return (string)$stream;
    }

    /**
     * @inheritdoc
     */
    public function encode(IOutputStream $stream): void
    {
        $this->contentType->encode($stream);
        ProtocolVersion::Version12->encode($stream);
        $stream->writeUnsignedInt16(strlen($this->bytes));
        $stream->write($this->bytes);
    }

    /**
     * @inheritdoc
     */
    public static function decode(IInputStream $stream, ?Context $context = null): static
    {
        $contentType = ContentType::decode($stream);
        ProtocolVersion::decode($stream);
        $data = $stream->read($stream->readUnsignedInt16());

        return new Record($contentType, $data);
    }
}
