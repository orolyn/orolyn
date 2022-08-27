<?php

namespace Orolyn\Net\Security\TLS\Crypto;

use Orolyn\ByteConverter;
use Orolyn\Endian;
use Orolyn\Net\Security\TLS\Structure\HkdfLabel;
use Orolyn\Net\Security\TLS\Structure\HkdfLabelType;

class HKDF
{
    public function __construct(
        public readonly string $algorithm
    ) {
    }

    public function extract(string $salt, string $key): string
    {
        return hex2bin(hash_hmac($this->algorithm, $key, $salt));
    }

    public function expand(): string
    {

    }

    public function expandLabel(string $key, HkdfLabelType $type, string $context, int $length): string
    {
        $info = new HkdfLabel(
            $length,
            $type,
            $context
        );

        $output = '';
        for ($keyBlock = '', $blockIndex = 1; ! isset($output[$length - 1]); $blockIndex++)
        {
            $keyBlock = hash_hmac($this->algorithm, $keyBlock.$info.chr($blockIndex), $key, true);
            $output .= $keyBlock;
        }

        return substr($output, 0, $length);
    }
}
