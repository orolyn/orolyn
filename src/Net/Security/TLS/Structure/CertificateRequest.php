<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\Net\Security\TLS\Context;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;
use Orolyn\Net\Security\Structure\Version13\Random13;

/**
 * struct {
 *     opaque certificate_request_context<0..2^8-1>;
 *     Extension extensions<2..2^16-1>;
 * } CertificateRequest;
 */
class CertificateRequest extends Structure
{
    private readonly int $length;

    public function __construct(
        public readonly string $context,
        public readonly ExtensionList $extensions,
    ) {
        $this->length = strlen($context);
    }

    /**
     * @inheritdoc
     */
    public function encode(IOutputStream $stream): void
    {
        $stream->writeUnsignedInt8($this->length);
        $stream->write($this->context);
        $this->extensions->encode($stream);
    }

    /**
     * @inheritdoc
     */
    public static function decode(IInputStream $stream, ?Context $context = null): static
    {
        return new CertificateRequest(
            $stream->read($stream->readUnsignedInt8()),
            ExtensionList::decode($stream, $context)
        );
    }
}
