<?php

namespace Orolyn\Net\Security;

use Exception;
use Orolyn\Endian;
use Orolyn\IO\IStream;
use Orolyn\IO\ProxyInputStream;
use Orolyn\IO\ProxyOutputStream;
use Orolyn\Net\Security\TLS\Context;
use Orolyn\Net\Security\TLS\Crypto\Encryption;
use Orolyn\Net\Security\TLS\Crypto\EncryptionMode;
use Orolyn\Net\Security\TLS\Crypto\Key\PublicKey;
use Orolyn\Net\Security\TLS\Crypto\Key\SecretKey;
use Orolyn\Net\Security\TLS\Crypto\KeyExchange;
use Orolyn\Net\Security\TLS\RecordLayer;
use Orolyn\Net\Security\TLS\Structure\CipherSuite;
use Orolyn\Net\Security\TLS\Structure\CipherSuiteList;
use Orolyn\Net\Security\TLS\Structure\Extension;
use Orolyn\Net\Security\TLS\Structure\ExtensionList;
use Orolyn\Net\Security\TLS\Structure\ClientHello;
use Orolyn\Net\Security\TLS\Structure\ContentType;
use Orolyn\Net\Security\TLS\Structure\ExtensionType;
use Orolyn\Net\Security\TLS\Structure\Handshake;
use Orolyn\Net\Security\TLS\Structure\HandshakeType;
use Orolyn\Net\Security\TLS\Structure\KeyShareEntry;
use Orolyn\Net\Security\TLS\Structure\KeyShareEntryVector;
use Orolyn\Net\Security\TLS\Structure\NamedGroup;
use Orolyn\Net\Security\TLS\Structure\NamedGroupVector;
use Orolyn\Net\Security\TLS\Structure\ProtocolVersion;
use Orolyn\Net\Security\TLS\Structure\ProtocolVersionList;
use Orolyn\Net\Security\TLS\Structure\Random;
use Orolyn\Net\Security\TLS\Structure\ServerName;
use Orolyn\Net\Security\TLS\Structure\ServerNameVector;
use Orolyn\Net\Security\TLS\Structure\SessionId;
use Orolyn\Net\Security\TLS\Structure\SignatureScheme;
use Orolyn\Net\Security\TLS\Structure\SignatureSchemeVector;
use Orolyn\SecureRandom;
use Orolyn\Security\Cryptography\SymmetricAlgorithm;
use SodiumException;

class SecureStream
{
    private RecordLayer $recordLayer;
    private Context $context;

    /**
     * @param IStream $stream
     */
    public function __construct(
        private IStream $stream
    ) {
        $this->context = new Context();

        // Use proxies to override the inner stream endian.
        $input = new ProxyInputStream($this->stream);
        $input->setEndian(Endian::BigEndian);

        $output = new ProxyOutputStream($this->stream);
        $output->setEndian(Endian::BigEndian);

        $this->recordLayer = new RecordLayer($input, $output, $this->context);
    }

    public function authenticateAsServer(): void
    {

    }

    public function authenticateAsClient(): void
    {
        $secretKey = SecretKey::generate();

        $this->context->clientRandom = new Random(SecureRandom::generateBytes(32));
        $this->context->supportedCipherSuites = CipherSuiteList::getModernCipherSuiteList();
        $this->context->isServer = false;

        $clientHelloHandshake = $this->createClientHandshake(
            $this->context->clientRandom,
            $this->context->supportedCipherSuites,
            $secretKey
        );

        $this->recordLayer->send(
            ContentType::Handshake,
            $clientHelloHandshake
        );

        /** @var Handshake $serverHelloHandshake */
        $serverHelloHandshake = $this->recordLayer->require(ContentType::Handshake, HandshakeType::ServerHello);
        $serverHelloHandshake->assertType(HandshakeType::ServerHello);

        if (HandshakeType::ServerHello !== $serverHelloHandshake->handshakeType) {
            throw new Exception();
        }

        $serverHello = $serverHelloHandshake->serverHello;

        if (!$this->context->supportedCipherSuites->contains($serverHello->cipherSuite)) {
            throw new Exception();
        }

        $this->context->serverRandom = $serverHello->random;
        $this->context->cipherSuite = $serverHello->cipherSuite;

        if (null === $keyShareExtension = $serverHello->extensions->getExtension(ExtensionType::KeyShare)) {
            throw new Exception();
        }

        $this->recordLayer->encryption = new Encryption(
            EncryptionMode::Client,
            $this->context->cipherSuite,
            KeyExchange::create(
                $this->context->cipherSuite,
                $clientHelloHandshake,
                $serverHelloHandshake,
                $secretKey,
                new PublicKey($keyShareExtension->keyShareEntry->keyExchange)
            )
        );

        $this->recordLayer->receive(ContentType::ChangeCipherSpec); // ContentType::ChangeCipherSpec

        /** @var Handshake $handshake */
        $handshake = $this->recordLayer->require(ContentType::Handshake, HandshakeType::EncryptedExtensions);

        var_dump('CERTIFICATE');

        /** @var Handshake $handshake */
        if (null !== $handshake = $this->recordLayer->receive(ContentType::Handshake, HandshakeType::Certificate)) {
            $serverCertificate = $handshake->certificate;

            /** @var Handshake $handshake */
            $handshake = $this->recordLayer->require(ContentType::Handshake, HandshakeType::CertificateVerify);
            $serverCertificateVerify = $handshake->certificateVerify;

            var_dump($serverCertificateVerify);
        }

        $handshake = $this->recordLayer->require(ContentType::Handshake, HandshakeType::Finished);

        //$plain = sodium_crypto_aead_aes256gcm_decrypt($cipherRecord->bytes, $cipherRecord->getHeader(), $exchange->serverIv, $exchange->serverKey);
        //var_dump(bin2hex($plain));
    }

    /**
     * @param Random $random
     * @param CipherSuiteList $supportedCipherSuites
     * @param SecretKey $secretKey
     * @return Handshake
     */
    private function createClientHandshake(
        Random $random,
        CipherSuiteList $supportedCipherSuites,
        SecretKey $secretKey
    ): Handshake {
        return new Handshake(
            HandshakeType::ClientHello,
            new ClientHello(
                ProtocolVersion::Version12,
                $random,
                SessionId::generate(),
                $supportedCipherSuites,
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
                                        $secretKey->getPublicKey()
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