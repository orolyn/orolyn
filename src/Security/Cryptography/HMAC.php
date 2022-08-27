<?php

namespace Orolyn\Security\Cryptography;

class HMAC extends KeyedHashAlgorithm
{
    public function __construct(HashAlgorithmMethod $algorithmMethod, ?string $key = null)
    {
        if (empty($key)) {
            $key = str_pad('', $algorithmMethod->getHashLength(), "\x00");
        }

        parent::__construct($algorithmMethod, $key);
    }

    /**
     * @inheritdoc
     */
    protected function doHash(HashAlgorithmMethod $algorithmMethod, string $data): string
    {
        return hash_hmac($algorithmMethod->value, $data, $this->key, true);
    }

    /**
     * @param HashAlgorithmMethod $algorithmMethod
     * @param string $key
     * @return HashAlgorithm
     */
    public static function create(HashAlgorithmMethod $algorithmMethod, string $key = "\x00"): HashAlgorithm
    {
        return match ($algorithmMethod) {
            HashAlgorithmMethod::MD5 => new HMACMD5($key),
            HashAlgorithmMethod::SHA1 => new HMACSHA1($key),
            HashAlgorithmMethod::SHA256 => new HMACSHA256($key),
            HashAlgorithmMethod::SHA384 => new HMACSHA384($key),
            HashAlgorithmMethod::SHA512 => new HMACSHA512($key),
            HashAlgorithmMethod::RIPEMD160 => new HMACRIPEMD160($key)
        };
    }
}
