<?php

namespace Orolyn\Net\Security\TLS\Crypto;

use Orolyn\Collection\IList;
use Orolyn\Net\Security\TLS\Crypto\Key\PublicKey;
use Orolyn\Net\Security\TLS\Crypto\Key\SecretKey;
use Orolyn\Net\Security\TLS\Structure\CipherSuite;
use Orolyn\Net\Security\TLS\Structure\Handshake;
use Orolyn\Net\Security\TLS\Structure\HkdfLabel;
use Orolyn\Net\Security\TLS\Structure\HkdfLabelType;
use Orolyn\Net\Security\TLS\Structure\KeyShareEntry;
use Orolyn\Security\Cryptography\Hash;
use Orolyn\Security\Cryptography\HashAlgorithm;
use Orolyn\Security\Cryptography\HKDF;

class HandshakeKeys extends Keys
{
    public readonly Hash $clientSecret;
    public readonly Hash $serverSecret;
    public readonly Hash $handshakeSecret;

    public function __construct(
        public readonly HashAlgorithm $hashAlgorithm,
        IList $handshakes,
        SecretKey $secretKey,
        PublicKey $publicKey
    ) {
        $handshakeHash = $hashAlgorithm->computeHash($handshakes->join());
        $hkdf = new HKDF($hashAlgorithm->algorithmMethod);

        $derivedSecret = self::expandLabel(
            $hkdf,
            $hkdf->extract(str_pad('', 48, "\x00"), "\x00"),
            HkdfLabelType::DERIVED,
            $hashAlgorithm->computeHash(''),
            48
        );

        $this->handshakeSecret = $hkdf->extract($secretKey->createSharedSecret($publicKey), $derivedSecret);

        $this->clientSecret = self::expandLabel(
            $hkdf,
            $this->handshakeSecret,
            HkdfLabelType::C_HS_TRAFFIC,
            $handshakeHash,
            48
        );

        $this->serverSecret = self::expandLabel(
            $hkdf,
            $this->handshakeSecret,
            HkdfLabelType::S_HS_TRAFFIC,
            $handshakeHash,
            48
        );

        parent::__construct(
            self::expandLabel($hkdf, $this->clientSecret, HkdfLabelType::KEY, '', 32),
            self::expandLabel($hkdf, $this->clientSecret, HkdfLabelType::IV,  '', 12),
            self::expandLabel($hkdf, $this->serverSecret, HkdfLabelType::KEY, '', 32),
            self::expandLabel($hkdf, $this->serverSecret, HkdfLabelType::IV,  '', 12)
        );
    }
}
