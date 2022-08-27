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
use Orolyn\Security\Cryptography\HashAlgorithm;
use Orolyn\Security\Cryptography\HKDF;

class ApplicationKeys extends Keys
{
    public function __construct(
        HandshakeKeys $handshakeKeys,
        IList $handshakes
    ) {
        $hashAlgorithm = $handshakeKeys->hashAlgorithm;
        $handshakeHash = $hashAlgorithm->computeHash($handshakes->join());
        $hkdf = new HKDF($hashAlgorithm->algorithmMethod);

        $derivedSecret = self::expandLabel(
            $hkdf,
            $handshakeKeys->handshakeSecret,
            HkdfLabelType::DERIVED,
            $hashAlgorithm->computeHash(''),
            48
        );

        $masterSecret = $hkdf->extract(str_pad('', 48, "\x00"), $derivedSecret);

        $clientSecret = self::expandLabel($hkdf, $masterSecret, HkdfLabelType::C_AP_TRAFFIC, $handshakeHash, 48);
        $serverSecret = self::expandLabel($hkdf, $masterSecret, HkdfLabelType::S_AP_TRAFFIC, $handshakeHash, 48);

        parent::__construct(
            self::expandLabel($hkdf, $clientSecret, HkdfLabelType::KEY, '', 32),
            self::expandLabel($hkdf, $clientSecret, HkdfLabelType::IV,  '', 12),
            self::expandLabel($hkdf, $serverSecret, HkdfLabelType::KEY, '', 32),
            self::expandLabel($hkdf, $serverSecret, HkdfLabelType::IV,  '', 12)
        );
    }
}
