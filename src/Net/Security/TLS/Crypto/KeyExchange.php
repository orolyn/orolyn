<?php

namespace Orolyn\Net\Security\TLS\Crypto;

use Orolyn\Net\Security\TLS\Crypto\Key\PublicKey;
use Orolyn\Net\Security\TLS\Crypto\Key\SecretKey;
use Orolyn\Net\Security\TLS\Structure\CipherSuite;
use Orolyn\Net\Security\TLS\Structure\Handshake;
use Orolyn\Net\Security\TLS\Structure\HkdfLabel;
use Orolyn\Net\Security\TLS\Structure\HkdfLabelType;
use Orolyn\Net\Security\TLS\Structure\KeyShareEntry;
use Orolyn\Security\Cryptography\HKDF;

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
        $hashAlgorithm = $cipherSuite->getHashAlgorithm();

        $helloHash = $hashAlgorithm->computeHash($clientHello . $serverHello);
        $hkdf = new HKDF($cipherSuite->getHashAlgorithm()->algorithmMethod);

        $earlySecret = $hkdf->extract(str_pad('', 48, "\x00"), "\x00");
        $emptyHash = $hashAlgorithm->computeHash('');
        $derivedSecret = self::expandLabel($hkdf, $earlySecret, HkdfLabelType::DERIVED, $emptyHash, 48);
        $handshakeSecret = $hkdf->extract($secretKey->createSharedSecret($publicKey), $derivedSecret);

        $clientSecret = self::expandLabel($hkdf, $handshakeSecret, HkdfLabelType::C_HS_TRAFFIC, $helloHash, 48);
        $serverSecret = self::expandLabel($hkdf, $handshakeSecret, HkdfLabelType::S_HS_TRAFFIC, $helloHash, 48);

        return new KeyExchange(
            self::expandLabel($hkdf, $clientSecret, HkdfLabelType::KEY, '', 32),
            self::expandLabel($hkdf, $clientSecret, HkdfLabelType::IV,  '', 12),
            self::expandLabel($hkdf, $serverSecret, HkdfLabelType::KEY, '', 32),
            self::expandLabel($hkdf, $serverSecret, HkdfLabelType::IV,  '', 12)
        );
    }

    private static function expandLabel(
        HKDF $hkdf,
        string $key,
        HkdfLabelType $type,
        string $context,
        int $length
    ): string {
        //return $hkdf->expandLabel($key, $type, $context, $length);

        return $hkdf->expand(
            $key,
            $length,
            new HkdfLabel(
                $length,
                $type,
                $context
            )
        );
    }
}
