<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;
use OutOfRangeException;
use RuntimeException;

/**
 * struct {
 *     NameType name_type;
 *         select (name_type) {
 *         case host_name: HostName;
 *     } name;
 * } ServerName;
 *
 * enum {
 *     host_name(0), (255)
 * } NameType;
 *
 * opaque HostName<1..2^16-1>;
 */
class ServerName extends Structure
{
    public readonly int $length;

    public function __construct(
        public readonly string $name = ''
    ) {
        $this->length = strlen($name);
    }

    /**
     * @inheritdoc
     */
    public function encode(IOutputStream $stream): void
    {
        $stream->writeUnsignedInt8(0); // name type: host_name
        $stream->writeUnsignedInt16($this->length);
        $stream->write($this->name);
    }

    /**
     * @inheritdoc
     */
    public static function decode(IInputStream $stream, ?bool $server = null): static
    {
        if ($stream->readUnsignedInt8() !== 0) {
            throw new RuntimeException('Invalid name type');
        }

        return new ServerName($stream->read($stream->readUnsignedInt16()));
    }
}
