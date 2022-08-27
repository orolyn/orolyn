<?php

namespace Orolyn\Security\Cryptography;

enum HashAlgorithmMethod: string
{
    case SHA1 = 'sha1';
    case SHA256 = 'sha256';
    case SHA384 = 'sha384';
    case SHA512 = 'sha512';
    case MD5 = 'md5';
    case RIPEMD160 = 'ripemd160';

    /**
     * @return int
     */
    public function getHashLength(): int
    {
        return match ($this) {
            self::MD5           => 16,
            self::RIPEMD160,
            self::SHA1          => 20,
            self::SHA256        => 32,
            self::SHA384        => 48,
            self::SHA512        => 64
        };
    }
}

