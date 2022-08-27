<?php

namespace Orolyn\Security\Cryptography;

class SymmetricAlgorithm
{
    public function __construct(
        public readonly CipherMethod $method,
        public readonly string $key,
        public readonly string $iv = '',
        public readonly int $tagLength = 16,
        public readonly string $associatedData = ''
    ) {
    }

    /**
     * @param string $data
     * @param string|null $iv
     * @param string|null $associatedData
     * @return false|string
     */
    public function encrypt(string $data, ?string $iv = null, ?string $associatedData = null): false|string
    {
        $iv = $iv ?? $this->iv;

        if ($this->method->isAead()) {
            $associatedData = $associatedData ?? $this->associatedData;
            $tagLength = $this->tagLength;
        } else {
            $associatedData = '';
            $tagLength = 0;
        }

        $encrypted = openssl_encrypt(
            $data,
            $this->method->value,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            $associatedData,
            $tagLength
        );

        return $encrypted . $tag;
    }

    /**
     * @param string $data
     * @param string|null $iv
     * @param string|null $associatedData
     * @return false|string
     */
    public function decrypt(string $data, ?string $iv = null, ?string $associatedData = null): false|string
    {
        $iv = $iv ?? $this->iv;

        if ($this->method->isAead()) {
            $associatedData = $associatedData ?? $this->associatedData;
            $tag = substr($data, -$this->tagLength);
            $data = substr($data, 0, -$this->tagLength);
        } else {
            $associatedData = '';
            $tag = '';
        }

        return openssl_decrypt(
            $data,
            $this->method->value,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            $associatedData
        );
    }
}
