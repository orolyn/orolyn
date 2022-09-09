<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\Net\Security\TLS\Context;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;

/**
 * struct {
 *     SignatureScheme algorithm;
 *     opaque signature<0..2^16-1>;
 * } CertificateVerify;
 */
class CertificateVerify extends Structure
{
    private readonly int $length;

    public function __construct(
        public readonly SignatureScheme $algorithm,
        public readonly string $signature
    ) {
        $this->length = strlen($signature);
    }

    /**
     * @inheritdoc
     */
    public function encode(IOutputStream $stream): void
    {
        $this->algorithm->encode($stream);
        $stream->writeUnsignedInt16($this->length);
        $stream->write($this->signature);
    }

    /**
     * @inheritdoc
     */
    public static function decode(IInputStream $stream, ?Context $context = null): static
    {
        return new CertificateVerify(
            SignatureScheme::decode($stream, $context),
            $stream->read($stream->readUnsignedInt16())
        );
    }
}
