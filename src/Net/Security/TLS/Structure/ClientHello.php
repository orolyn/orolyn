<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\Net\Security\TLS\Context;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;
use Orolyn\Net\Security\TLS\Crypto\Key\SecretKey;
use Orolyn\SecureRandom;
use SodiumException;

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

    /**
     * @param Context $context
     * @throws SodiumException
     */
    public static function createHandshake(Context $context): Handshake
    {
        return new Handshake(
            HandshakeType::ClientHello,
            new ClientHello(
                ProtocolVersion::Version12,
                $context->clientRandom,
                SessionId::generate(),
                $context->supportedCipherSuites,
                new ExtensionList(
                    [
                        new Extension(
                            ExtensionType::SupportedVersions,
                            new ProtocolVersionList(
                                [
                                    ProtocolVersion::Version13
                                ]
                            )
                        ),
                        new Extension(
                            ExtensionType::ServerName,
                            new ServerNameVector(
                                [
                                    new ServerName('www.example.com')
                                ]
                            )
                        ),
                        new Extension(
                            ExtensionType::SignatureAlgorithms,
                            new SignatureSchemeVector(
                                [
                                    SignatureScheme::ED25519
                                ]
                            )
                        ),
                        new Extension(
                            ExtensionType::SupportedGroups,
                            new NamedGroupVector(
                                [
                                    NamedGroup::X25519
                                ]
                            )
                        ),
                        new Extension(
                            ExtensionType::KeyShare,
                            new KeyShareEntryVector(
                                [
                                    new KeyShareEntry(
                                        NamedGroup::X25519,
                                        $context->secretKey->getPublicKey()
                                    )
                                ]
                            )
                        )
                    ]
                )
            )
        );
    }
}
