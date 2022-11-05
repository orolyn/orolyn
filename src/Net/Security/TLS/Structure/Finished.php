<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\Net\Security\TLS\Context;
use Orolyn\ArgumentException;
use Orolyn\BitConverter;
use Orolyn\IEquatable;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;

/**
 * struct {
 *     opaque verify_data[Hash.length];
 * } Finished;
 */
class Finished extends Structure
{
    public readonly int $length;

    public function __construct(
        public readonly string $verifyData
    ) {
        $this->length = strlen($this->verifyData);
    }

    /**
     * @inheritdoc
     */
    public function encode(IOutputStream $stream): void
    {
        $stream->write($this->verifyData);
    }

    /**
     * @inheritdoc
     */
    public static function decode(IInputStream $stream, ?Context $context = null): static
    {
        return new Finished($stream->read($context->cipherSuite->getHashAlgorithm()->getHashLength()));
    }
}
