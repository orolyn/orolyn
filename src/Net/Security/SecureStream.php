<?php

namespace Orolyn\Net\Security;

use Exception;
use Orolyn\Endian;
use Orolyn\IO\IStream;
use Orolyn\IO\ProxyInputStream;
use Orolyn\IO\ProxyOutputStream;
use Orolyn\Net\Security\TLS\Context;
use Orolyn\Net\Security\TLS\Crypto\ApplicationKeys;
use Orolyn\Net\Security\TLS\Crypto\Encryption;
use Orolyn\Net\Security\TLS\Crypto\EncryptionMode;
use Orolyn\Net\Security\TLS\Crypto\HandshakeKeys;
use Orolyn\Net\Security\TLS\Crypto\Key\PublicKey;
use Orolyn\Net\Security\TLS\Crypto\Key\SecretKey;
use Orolyn\Net\Security\TLS\Crypto\Keys;
use Orolyn\Net\Security\TLS\RecordLayer;
use Orolyn\Net\Security\TLS\Structure\Alert;
use Orolyn\Net\Security\TLS\Structure\AlertDescription;
use Orolyn\Net\Security\TLS\Structure\AlertLevel;
use Orolyn\Net\Security\TLS\Structure\ChangeCipherSpec;
use Orolyn\Net\Security\TLS\Structure\CipherSuite;
use Orolyn\Net\Security\TLS\Structure\CipherSuiteList;
use Orolyn\Net\Security\TLS\Structure\Extension;
use Orolyn\Net\Security\TLS\Structure\ExtensionList;
use Orolyn\Net\Security\TLS\Structure\ClientHello;
use Orolyn\Net\Security\TLS\Structure\ContentType;
use Orolyn\Net\Security\TLS\Structure\ExtensionType;
use Orolyn\Net\Security\TLS\Structure\Finished;
use Orolyn\Net\Security\TLS\Structure\Handshake;
use Orolyn\Net\Security\TLS\Structure\HandshakeType;
use Orolyn\Net\Security\TLS\Structure\HkdfLabelType;
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
use Orolyn\Security\Cryptography\HashAlgorithmMethod;
use Orolyn\Security\Cryptography\HKDF;
use Orolyn\Security\Cryptography\HMAC;
use Orolyn\Security\Cryptography\SHA384;
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
        $this->context->clientRandom = new Random(SecureRandom::generateBytes(32));
        $this->context->supportedCipherSuites = CipherSuiteList::getModernCipherSuiteList();
        $this->context->isServer = false;
        $this->context->secretKey = SecretKey::generate();

        $this->recordLayer->sendHandshake(ClientHello::createHandshake($this->context));
        $handshake = $this->recordLayer->requireHandshake(HandshakeType::ServerHello);

        if (!$this->context->supportedCipherSuites->contains($handshake->serverHello->cipherSuite)) {
            throw new Exception();
        }

        $this->context->serverRandom = $handshake->serverHello->random;
        $this->context->cipherSuite = $handshake->serverHello->cipherSuite;

        if (null === $keyShareExtension = $handshake->serverHello->extensions->getExtension(ExtensionType::KeyShare)) {
            throw new Exception();
        }

        $remoteKey = new PublicKey($keyShareExtension->keyShareEntry->keyExchange);

        $this->recordLayer->encryption = new Encryption(
            EncryptionMode::Client,
            $this->context->cipherSuite,
            $handshakeKeys = new HandshakeKeys(
                $this->context->cipherSuite->getHashAlgorithm(),
                $this->recordLayer->getCurrentHandshakes(),
                $this->context->secretKey,
                $remoteKey
            )
        );

        $this->recordLayer->receiveChangeCipherSpec(); // ContentType::ChangeCipherSpec
        $handshake = $this->recordLayer->requireHandshake(HandshakeType::EncryptedExtensions);

        if (null !== $handshake = $this->recordLayer->receiveHandshake(HandshakeType::Certificate)) {
            $serverCertificate = $handshake->certificate;

            $handshake = $this->recordLayer->requireHandshake(HandshakeType::CertificateVerify);
            $serverCertificateVerify = $handshake->certificateVerify;
        }

        $handshake = $this->recordLayer->requireHandshake(HandshakeType::Finished);
        $applicationKeys = new ApplicationKeys($handshakeKeys, $this->recordLayer->getCurrentHandshakes());

        $this->recordLayer->sendChangeCipherSpec();

        $finishedKey = Keys::expandLabel(
            new HKDF(HashAlgorithmMethod::SHA384),
            $handshakeKeys->clientSecret,
            HkdfLabelType::FINISHED,
            '',
            48
        );
        $finishedHash = (new SHA384())->computeHash($this->recordLayer->getCurrentHandshakes()->join());
        $verifyData = HMAC::create(HashAlgorithmMethod::SHA384, $finishedKey)->computeHash($finishedHash);

        $this->recordLayer->sendHandshake(
            new Handshake(
                HandshakeType::Finished,
                new Finished(
                    $verifyData
                )
            )
        );

        $this->recordLayer->encryption = new Encryption(
            EncryptionMode::Client,
            $this->context->cipherSuite,
            $applicationKeys
        );

        var_dump($this->recordLayer->requireAlert());



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
