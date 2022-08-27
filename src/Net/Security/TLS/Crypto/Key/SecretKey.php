<?php

namespace Orolyn\Net\Security\TLS\Crypto\Key;

use Orolyn\ArgumentException;
use Orolyn\SecureRandom;
use SodiumException;

class SecretKey
{
    /**
     * @param string $key
     */
    public function __construct(
        private readonly string $key
    ) {
        if (strlen($key) !== SODIUM_CRYPTO_BOX_SECRETKEYBYTES) {
            throw new ArgumentException(sprintf('Expected %s bytes', SODIUM_CRYPTO_BOX_SECRETKEYBYTES));
        }
    }

    /**
     * @return SecretKey
     * @throws SodiumException
     */
    public static function generate(): SecretKey
    {
        return new SecretKey(
            sodium_crypto_box_secretkey(
                sodium_crypto_box_seed_keypair(
                    SecureRandom::generateBytes(32)
                )
            )
        );
    }

    public function createSharedSecret(PublicKey $publicKey): string
    {
        return sodium_crypto_scalarmult($this->key, $publicKey);
    }

    /**
     * @return PublicKey
     * @throws SodiumException
     */
    public function getPublicKey(): PublicKey
    {
        return new PublicKey(sodium_crypto_box_publickey_from_secretkey($this->key));
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->key;
    }
}
