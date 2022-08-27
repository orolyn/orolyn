<?php

namespace Orolyn\Net\Security\TLS\Crypto;

use Orolyn\Net\Security\TLS\Structure\HkdfLabel;
use Orolyn\Net\Security\TLS\Structure\HkdfLabelType;
use Orolyn\Security\Cryptography\Hash;
use Orolyn\Security\Cryptography\HKDF;

abstract class Keys
{
    protected function __construct(
        public readonly string $clientKey,
        public readonly string $clientIv,
        public readonly string $serverKey,
        public readonly string $serverIv
    ) {
    }

    /**
     * @param HKDF $hkdf
     * @param string $key
     * @param HkdfLabelType $type
     * @param string $context
     * @param int $length
     * @return Hash
     */
    public static function expandLabel(
        HKDF $hkdf,
        string $key,
        HkdfLabelType $type,
        string $context,
        int $length
    ): Hash {
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
