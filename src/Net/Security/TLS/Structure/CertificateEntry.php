<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\Net\Security\TLS\Context;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;

/**
 * struct {
 *     select (certificate_type) {
 *         case RawPublicKey:
 *             // From RFC 7250 ASN.1_subjectPublicKeyInfo
 *             opaque ASN1_subjectPublicKeyInfo<1..2^24-1>;
 *
 *         case X509:
 *             opaque cert_data<1..2^24-1>;
 *     };
 *     Extension extensions<0..2^16-1>;
 * } CertificateEntry;
 */
class CertificateEntry extends Structure
{
    private readonly int $length;

    public function __construct(
        public readonly string $info,
        public readonly ExtensionList $extensions,
    ) {
        $this->length = strlen($info);
    }

    /**
     * @inheritdoc
     */
    public function encode(IOutputStream $stream): void
    {
        $stream->writeUnsignedInt24($this->length);
        $stream->write($this->info);
        $this->extensions->encode($stream);
    }

    /**
     * @inheritdoc
     */
    public static function decode(IInputStream $stream, ?Context $context = null): static
    {
        return new CertificateEntry(
            $stream->read($stream->readUnsignedInt24()),
            ExtensionList::decode($stream, $context)
        );
    }
}
