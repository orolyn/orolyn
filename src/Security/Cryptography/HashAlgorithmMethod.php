<?php

namespace Orolyn\Security\Cryptography;

use Orolyn\IO\Binary;

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
        static $sizes;

        $sizes = $sizes ?? [];

        if (!isset($sizes[$this->value])) {
            $sizes[$this->value] = Binary::getLength(hash($this->value, "\x00", true));
        }

        return $sizes[$this->value];
    }
}

