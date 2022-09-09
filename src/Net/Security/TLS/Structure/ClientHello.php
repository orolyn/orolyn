<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\Net\Security\TLS\Context;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;
use Orolyn\SecureRandom;

/**
 * struct {
 *     ProtocolVersion client_version;
 *     Random random;
 *     SessionID session_id;
 *     CipherSuite cipher_suites<2..2^16-2>;
 *     CompressionMethod compression_methods<1..2^8-1>;
 *     select (extensions_present) {
 *         case false:
 *             struct {};
 *         case true:
 *             Extension extensions<0..2^16-1>;
 *     };
 * } ClientHello;
 */
class ClientHello extends Structure
{
    public function __construct(
        public readonly ProtocolVersion $protocolVersion,
        public readonly Random $random,
        public readonly SessionId $sessionId,
        public readonly CipherSuiteList $cipherSuites,
        public readonly ExtensionList $extensions
    ) {
    }

    /**
     * @inheritdoc
     */
    public function encode(IOutputStream $stream): void
    {
        $this->protocolVersion->encode($stream); // legacy version
        $this->random->encode($stream);
        $this->sessionId->encode($stream); // legacy session id
        $this->cipherSuites->encode($stream);
        $stream->write("\x01\x00"); // legacy compression method
        $this->extensions->encode($stream);
    }

    /**
     * @inheritdoc
     */
    public static function decode(IInputStream $stream, ?Context $context = null): static
    {
        $protocolVersion = ProtocolVersion::decode($stream, $context); // legacy version
        $random = Random::decode($stream, $context);
        $sessionId = SessionId::decode($stream, $context);
        $stream->read(2);
        $cipherSuites = CipherSuiteList::decode($stream, $context);
        $extensions = ExtensionList::decode($stream, $context);

        return new ClientHello(
            $protocolVersion,
            $random,
            $sessionId,
            $cipherSuites,
            $extensions
        );
    }
}
