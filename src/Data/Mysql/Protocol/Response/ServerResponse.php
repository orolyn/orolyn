<?php

namespace Orolyn\Data\Mysql\Protocol\Response;

use Orolyn\Data\Mysql\MysqlHandle;
use Orolyn\Data\Mysql\MysqlOptions;
use Orolyn\Data\Mysql\Protocol\Capability;
use Orolyn\IO\ByteStream;

final class ServerResponse
{
    public function __construct(
        private MysqlOptions $options,
        private int $capabilities,
        private MysqlHandle $handle,
    ) {
    }

    public function decode(ByteStream $stream): OK|Error|LocalInfile|ResultSet
    {
        $tag = $stream->readUnsignedInt8();
        $stream->reset();

        if (
            (0xFE === $tag && $this->capabilities &
                Capability::CLIENT_DEPRECATE_EOF && $stream->getLength() < 0xFFFFFF) |
            (0x00 === $tag)
        ) {
            return OK::decode($stream, $this->capabilities);
        }

        if (0xFF === $tag) {
            // error
        }

        if (0xFB) {
            // local infile
        }

        return ResultSet::decode($stream, $this->capabilities, $this->handle);
    }
}
