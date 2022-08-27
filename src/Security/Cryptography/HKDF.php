<?php

namespace Orolyn\Security\Cryptography;

use Orolyn\IO\Binary;

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
     * @return Hash
     */
    public function expand(string $prk, int $outputLength, string $info): Hash
    {
        for ($output = '', $keyBlock = '', $blockIndex = 1; !isset($output[$outputLength - 1]); $blockIndex++)
        {
            $keyBlock = $this->extract($keyBlock.$info.chr($blockIndex), $prk);
            $output .= $keyBlock;
        }

        return new Hash(Binary::getSubstring($output, 0, $outputLength));
    }
}
