<?php

namespace Orolyn\Security\Cryptography;

use Orolyn\IO\Binary;
use Orolyn\Net\Security\TLS\Structure\HkdfLabel;
use Orolyn\Net\Security\TLS\Structure\HkdfLabelType;

class HKDF
{
    /**
     * @param HashAlgorithmMethod $algorithmMethod
     */
    public function __construct(
        public readonly HashAlgorithmMethod $algorithmMethod
    ) {
    }

    /**
     * @param string $ikm
     * @param string|null $salt
     * @return Hash
     */
    public function extract(string $ikm, ?string $salt = null): Hash
    {
        return HMAC::create($this->algorithmMethod, $salt)->computeHash($ikm);
    }

    /**
     * @param string $prk
     * @param int $outputLength
     * @param string $info
     * @return string
     */
    public function expand(string $prk, int $outputLength, string $info): string
    {
        for ($output = '', $keyBlock = '', $blockIndex = 1; !isset($output[$outputLength - 1]); $blockIndex++)
        {
            $keyBlock = $this->extract($keyBlock.$info.chr($blockIndex), $prk);
            $output .= $keyBlock;
        }

        return Binary::getSubstring($output, 0, $outputLength);
    }
}
