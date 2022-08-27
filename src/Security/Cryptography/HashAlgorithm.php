<?php

namespace Orolyn\Security\Cryptography;

use Orolyn\InvalidOperationException;
use Orolyn\IO\IInputStream;

abstract class HashAlgorithm
{
    /**
     * @param HashAlgorithmMethod $algorithmMethod
     */
    protected function __construct(
        public readonly HashAlgorithmMethod $algorithmMethod
    ) {
    }

    /**
     * @param HashAlgorithmMethod $algorithmMethod
     * @return HashAlgorithm
     */
    public static function create(HashAlgorithmMethod $algorithmMethod): HashAlgorithm
    {
        return match ($algorithmMethod) {
            HashAlgorithmMethod::MD5 => new MD5(),
            HashAlgorithmMethod::SHA1 => new SHA1(),
            HashAlgorithmMethod::SHA256 => new SHA256(),
            HashAlgorithmMethod::SHA384 => new SHA384(),
            HashAlgorithmMethod::SHA512 => new SHA512(),
            HashAlgorithmMethod::RIPEMD160 => new RIPEMD160()
        };
    }

    /**
     * @return int
     */
    public function getHashLength(): int
    {
        return $this->algorithmMethod->getHashLength();
    }

    /**
     * @param IInputStream|string $data
     * @return Hash
     */
    public function computeHash(IInputStream|string $data): Hash
    {
        if ($data instanceof IInputStream) {
            $incremental = $this instanceof KeyedHashAlgorithm
                ? IncrementalHash::createHMAC($this->algorithmMethod, $this->key)
                : IncrementalHash::createHash($this->algorithmMethod);

            $incremental->appendData($data);

            return $incremental->getCurrentHash();
        }

        return new Hash($this->doHash($this->algorithmMethod, $data));
    }

    /**
     * @param HashAlgorithmMethod $algorithmMethod
     * @param string $data
     * @return string
     */
    protected function doHash(HashAlgorithmMethod $algorithmMethod, string $data): string
    {
        return hash($algorithmMethod->value, $data, true);
    }
}
