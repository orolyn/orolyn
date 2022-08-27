<?php

namespace Orolyn\Net\Security\TLS\Crypto;

use Orolyn\Net\Security\TLS\Crypto\Key\PublicKey;
use Orolyn\Net\Security\TLS\Crypto\Key\SecretKey;
use Orolyn\Net\Security\TLS\Structure\CipherSuite;
use Orolyn\Net\Security\TLS\Structure\Handshake;
use Orolyn\Net\Security\TLS\Structure\HkdfLabelType;
use Orolyn\Net\Security\TLS\Structure\KeyShareEntry;

class KeyExchange
{
    public function __construct(
        public readonly string $clientKey,
        public readonly string $clientIv,
        public readonly string $serverKey,
        public readonly string $serverIv,
    ) {
    }

    public static function create(
        CipherSuite $cipherSuite,
        Handshake $clientHello,
        Handshake $serverHello,
        SecretKey $secretKey,
        PublicKey $publicKey
    ): KeyExchange {
        $helloHash = hash($cipherSuite->getHashAlgorithm(), $clientHello . $serverHello, true);
        $hkdf = new HKDF($cipherSuite->getHashAlgorithm());

        $earlySecret = $hkdf->extract("\x00", str_pad('', 48, "\x00"));
        $emptyHash = hash($cipherSuite->getHashAlgorithm(), '', true);
        $derivedSecret = $hkdf->expandLabel($earlySecret, HkdfLabelType::DERIVED, $emptyHash, 48);
        $handshakeSecret = $hkdf->extract($derivedSecret, $secretKey->createSharedSecret($publicKey));

        $clientSecret = $hkdf->expandLabel($handshakeSecret, HkdfLabelType::C_HS_TRAFFIC, $helloHash, 48);
        $serverSecret = $hkdf->expandLabel($handshakeSecret, HkdfLabelType::S_HS_TRAFFIC, $helloHash, 48);

        return new KeyExchange(
            $hkdf->expandLabel($clientSecret, HkdfLabelType::KEY, '', 32),
            $hkdf->expandLabel($clientSecret, HkdfLabelType::IV,  '', 12),
            $hkdf->expandLabel($serverSecret, HkdfLabelType::KEY, '', 32),
            $hkdf->expandLabel($serverSecret, HkdfLabelType::IV,  '', 12)
        );
    }
}
