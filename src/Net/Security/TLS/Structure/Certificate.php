<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;

/**
 * struct {
 *     opaque certificate_request_context<0..2^8-1>;
 *     CertificateEntry certificate_list<0..2^24-1>;
 * } Certificate;
 */
class Certificate extends Structure
{
    private readonly int $length;

    public function __construct(
        public readonly string $context,
        public readonly CertificateEntryList $certificateList,
    ) {
        $this->length = strlen($context);
    }

    /**
     * @inheritdoc
     */
    public function encode(IOutputStream $stream): void
    {
        $stream->writeUnsignedInt16($this->length);
        $stream->write($this->context);
        $this->certificateList->encode($stream);
    }

    /**
     * @inheritdoc
     */
    public static function decode(IInputStream $stream, ?bool $server = null): static
    {
        return new Certificate(
            $stream->read($stream->readUnsignedInt8()),
            CertificateEntryList::decode($stream, $server)
        );
    }
}
