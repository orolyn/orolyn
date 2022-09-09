<?php

namespace Orolyn\Security\Cryptography;

use Orolyn\IO\IInputStream;

class IncrementalHash
{
    /**
     * @var mixed|null
     */
    private mixed $context = null;

    /**
     * @param HashAlgorithmMethod $method
     * @param bool $hmac
     */
    final private function __construct(
        public readonly HashAlgorithmMethod $method,
        private bool $hmac,
        private ?string $key
    ) {
    }

    /**
     * @param HashAlgorithmMethod $method
     * @return IncrementalHash
     */
    public static function createHash(HashAlgorithmMethod $method): IncrementalHash
    {
        return new IncrementalHash($method, false, null);
    }

    /**
     * @param HashAlgorithmMethod $method
     * @param string $key
     * @return IncrementalHash
     */
    public static function createHMAC(HashAlgorithmMethod $method, string $key): IncrementalHash
    {
        return new IncrementalHash($method, true, $key);
    }

    /**
     * Appends data to the incremental hash. If data is a stream and length is null, the remainder of the stream
     * will be appended. If data is a stream and length is specified, the length of bytes will be appended from the
     * stream, blocking if there are insufficient bytes.
     *
     * @param IInputStream|string $data
     * @param int|null $length
     * @return void
     */
    public function appendData(IInputStream|string $data, ?int $length = null): void
    {
        $this->init();

        if ($data instanceof IInputStream) {
            if (null !== $length) {
                hash_update($this->context, $length);
            } else {
                while (!$data->isEndOfStream()) {
                    hash_update($this->context, $data->read($length));
                }
            }

            return;
        }

        hash_update($this->context, $data);
    }

    /**
     * @return Hash
     */
    public function getCurrentHash(): Hash
    {
        return new Hash(hash_final($this->context, true));
    }

    /**
     * @return Hash
     */
    public function getHashAndReset(): Hash
    {
        $hash = $this->getCurrentHash();
        $this->context = null;

        return $hash;
    }

    /**
     * @return void
     */
    private function init(): void
    {
        if (null !== $this->context) {
            return;
        }

        if ($this->hmac) {
            $this->context = hash_init($this->method->value, HASH_HMAC, $this->key);
        } else {
            $this->context = hash_init($this->method->value);
        }
    }
}
