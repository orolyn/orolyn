<?php

namespace Orolyn\Data\Mysql\Protocol\Authentication;

use Orolyn\ByteConverter;
use Orolyn\Data\Mysql\Protocol\Packet;

class AuthSwitchRequest
{
    public function __construct(
        public readonly int $statusTag,
        public readonly ?Authentication $authentication = null,
        public readonly ?int $step,
    ) {
    }

    public static function decode(Packet $packet): AuthSwitchRequest
    {
        $stream = $packet->payload;

        $statusTag = $stream->readUnsignedInt8();
        $authentication = null;

        $step = null;

        if (0x01 === $statusTag) {
            $step = $stream->readUnsignedInt8();
        } else {
            $authentication = Authentication::getPluginFromString(
                $stream->readNullTerminated(),
                $stream->read($stream->getBytesAvailable())
            );
        }

        return new self($statusTag, $authentication, $step);
    }

    public function isRestart(): bool
    {
        return null === $this->authentication;
    }

    public function isContinue(): bool
    {
        return 0x04 === $this->step;
    }
}
