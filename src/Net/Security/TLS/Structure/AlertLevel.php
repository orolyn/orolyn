<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\Net\Security\TLS\Context;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;

/**
 * enum { warning(1), fatal(2), (255) } AlertLevel;
 */
enum AlertLevel: int implements IStructure
{
    case Warning = 1;
    case Fatal   = 2;

    /**
     * @inheritdoc
     */
    public function encode(IOutputStream $stream): void
    {
        $stream->writeUnsignedInt8($this->value);
    }

    /**
     * @inheritdoc
     */
    public static function decode(IInputStream $stream, ?Context $context = null): static
    {
        return AlertLevel::from($stream->readUnsignedInt8());
    }
}
