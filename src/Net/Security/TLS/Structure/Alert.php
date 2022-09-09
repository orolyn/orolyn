<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\Net\Security\TLS\Context;
use Orolyn\IO\ByteStream;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;
use Orolyn\Net\Security\TLS\AlertException;

/**
 * struct {
 *     AlertLevel level;
 *     AlertDescription description;
 * } Alert;
 */
class Alert extends Structure
{
    public function __construct(
        public readonly AlertLevel $alertLevel,
        public readonly AlertDescription $alertDescription
    ) {
    }

    /**
     * @inheritdoc
     */
    public function encode(IOutputStream $stream): void
    {
        $this->alertLevel->encode($stream);
        $this->alertDescription->encode($stream);
    }

    /**
     * @inheritdoc
     */
    public static function decode(IInputStream $stream, ?Context $context = null): static
    {
        return new Alert(
            AlertLevel::decode($stream, $context),
            AlertDescription::decode($stream, $context)
        );
    }

    /**
     * @return AlertException
     */
    public function createException(): AlertException
    {
        return AlertException::create($this);
    }
}
