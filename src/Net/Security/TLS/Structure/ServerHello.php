<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\Net\Security\TLS\Context;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;
use Orolyn\SecureRandom;

/**
 * struct {
 *     ProtocolVersion legacy_version = 0x0303; // TLS v1.2
 *     Random random;
 *     opaque legacy_session_id_echo<0..32>;
 *     CipherSuite cipher_suite;
 *     uint8 legacy_compression_method = 0;
 *     Extension extensions<6..2^16-1>;
 * } ServerHello;
 */
class ServerHello extends Structure
{
    public function __construct(
        public readonly ProtocolVersion $protocolVersion,
        public readonly Random $random,
        public readonly SessionId $sessionId,
        public readonly CipherSuite $cipherSuite,
        public readonly ExtensionList $extensions
    ) {
    }

    /**
     * @inheritdoc
     */
    public function encode(IOutputStream $stream): void
    {
        ProtocolVersion::Version12->encode($stream); // legacy version
        $this->random->encode($stream);
        $this->sessionId->encode($stream); // legacy session id
        $this->cipherSuite->encode($stream);
        $stream->write("\x00"); // legacy compression method
        $this->extensions->encode($stream);
    }

    /**
     * @inheritdoc
     */
    public static function decode(IInputStream $stream, ?Context $context = null): static
    {
        $protocolVersion = ProtocolVersion::decode($stream, $context); // legacy version
        $random = Random::decode($stream, $context);
        $sessionId = SessionId::decode($stream, $context); // legacy session id
        $cipherSuite = CipherSuite::decode($stream, $context);
        $stream->read(); // legacy compression method
        $extensions = ExtensionList::decode($stream, $context);

        return new ServerHello(
            $protocolVersion,
            $random,
            $sessionId,
            $cipherSuite,
            $extensions
        );
    }
}
