<?php

namespace Orolyn\Net\Security\TLS\Crypto\Key;

use Orolyn\ArgumentException;

class PublicKey
{
    /**
     * @param string $key
     */
    public function __construct(
        private string $key
    ) {
        if (strlen($key) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
            throw new ArgumentException(sprintf('Expected %s bytes', SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES));
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->key;
    }
}
