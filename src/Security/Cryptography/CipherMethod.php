<?php

namespace Orolyn\Security\Cryptography;

use Orolyn\SecureRandom;

enum CipherMethod: string
{
    case AES_128_CBC = 'aes-128-cbc';
    case AES_128_CBC_HMAC_SHA1 = 'aes-128-cbc-hmac-sha1';
    case AES_128_CBC_HMAC_SHA256 = 'aes-128-cbc-hmac-sha256';
    case AES_128_CCM = 'aes-128-ccm';
    case AES_128_CCM_8 = 'aes-128-ccm-8';
    case AES_128_CFB = 'aes-128-cfb';
    case AES_128_CFB1 = 'aes-128-cfb1';
    case AES_128_CFB8 = 'aes-128-cfb8';
    case AES_128_CTR = 'aes-128-ctr';
    case AES_128_ECB = 'aes-128-ecb';
    case AES_128_GCM = 'aes-128-gcm';
    case AES_128_OCB = 'aes-128-ocb';
    case AES_128_OFB = 'aes-128-ofb';
    case AES_128_XTS = 'aes-128-xts';
    case AES_192_CBC = 'aes-192-cbc';
    case AES_192_CCM = 'aes-192-ccm';
    case AES_192_CFB = 'aes-192-cfb';
    case AES_192_CFB1 = 'aes-192-cfb1';
    case AES_192_CFB8 = 'aes-192-cfb8';
    case AES_192_CTR = 'aes-192-ctr';
    case AES_192_ECB = 'aes-192-ecb';
    case AES_192_GCM = 'aes-192-gcm';
    case AES_192_OCB = 'aes-192-ocb';
    case AES_192_OFB = 'aes-192-ofb';
    case AES_256_CBC = 'aes-256-cbc';
    case AES_256_CBC_HMAC_SHA1 = 'aes-256-cbc-hmac-sha1';
    case AES_256_CBC_HMAC_SHA256 = 'aes-256-cbc-hmac-sha256';
    case AES_256_CCM = 'aes-256-ccm';
    case AES_256_CFB = 'aes-256-cfb';
    case AES_256_CFB1 = 'aes-256-cfb1';
    case AES_256_CFB8 = 'aes-256-cfb8';
    case AES_256_CTR = 'aes-256-ctr';
    case AES_256_ECB = 'aes-256-ecb';
    case AES_256_GCM = 'aes-256-gcm';
    case AES_256_OCB = 'aes-256-ocb';
    case AES_256_OFB = 'aes-256-ofb';
    case AES_256_XTS = 'aes-256-xts';
    case ARIA_128_CBC = 'aria-128-cbc';
    case ARIA_128_CCM = 'aria-128-ccm';
    case ARIA_128_CFB = 'aria-128-cfb';
    case ARIA_128_CFB1 = 'aria-128-cfb1';
    case ARIA_128_CFB8 = 'aria-128-cfb8';
    case ARIA_128_CTR = 'aria-128-ctr';
    case ARIA_128_ECB = 'aria-128-ecb';
    case ARIA_128_GCM = 'aria-128-gcm';
    case ARIA_128_OFB = 'aria-128-ofb';
    case ARIA_192_CBC = 'aria-192-cbc';
    case ARIA_192_CCM = 'aria-192-ccm';
    case ARIA_192_CFB = 'aria-192-cfb';
    case ARIA_192_CFB1 = 'aria-192-cfb1';
    case ARIA_192_CFB8 = 'aria-192-cfb8';
    case ARIA_192_CTR = 'aria-192-ctr';
    case ARIA_192_ECB = 'aria-192-ecb';
    case ARIA_192_GCM = 'aria-192-gcm';
    case ARIA_192_OFB = 'aria-192-ofb';
    case ARIA_256_CBC = 'aria-256-cbc';
    case ARIA_256_CCM = 'aria-256-ccm';
    case ARIA_256_CFB = 'aria-256-cfb';
    case ARIA_256_CFB1 = 'aria-256-cfb1';
    case ARIA_256_CFB8 = 'aria-256-cfb8';
    case ARIA_256_CTR = 'aria-256-ctr';
    case ARIA_256_ECB = 'aria-256-ecb';
    case ARIA_256_GCM = 'aria-256-gcm';
    case ARIA_256_OFB = 'aria-256-ofb';
    case BF_CBC = 'bf-cbc';
    case BF_CFB = 'bf-cfb';
    case BF_ECB = 'bf-ecb';
    case BF_OFB = 'bf-ofb';
    case CAMELLIA_128_CBC = 'camellia-128-cbc';
    case CAMELLIA_128_CFB = 'camellia-128-cfb';
    case CAMELLIA_128_CFB1 = 'camellia-128-cfb1';
    case CAMELLIA_128_CFB8 = 'camellia-128-cfb8';
    case CAMELLIA_128_CTR = 'camellia-128-ctr';
    case CAMELLIA_128_ECB = 'camellia-128-ecb';
    case CAMELLIA_128_OFB = 'camellia-128-ofb';
    case CAMELLIA_192_CBC = 'camellia-192-cbc';
    case CAMELLIA_192_CFB = 'camellia-192-cfb';
    case CAMELLIA_192_CFB1 = 'camellia-192-cfb1';
    case CAMELLIA_192_CFB8 = 'camellia-192-cfb8';
    case CAMELLIA_192_CTR = 'camellia-192-ctr';
    case CAMELLIA_192_ECB = 'camellia-192-ecb';
    case CAMELLIA_192_OFB = 'camellia-192-ofb';
    case CAMELLIA_256_CBC = 'camellia-256-cbc';
    case CAMELLIA_256_CFB = 'camellia-256-cfb';
    case CAMELLIA_256_CFB1 = 'camellia-256-cfb1';
    case CAMELLIA_256_CFB8 = 'camellia-256-cfb8';
    case CAMELLIA_256_CTR = 'camellia-256-ctr';
    case CAMELLIA_256_ECB = 'camellia-256-ecb';
    case CAMELLIA_256_OFB = 'camellia-256-ofb';
    case CAST5_CBC = 'cast5-cbc';
    case CAST5_CFB = 'cast5-cfb';
    case CAST5_ECB = 'cast5-ecb';
    case CAST5_OFB = 'cast5-ofb';
    case CHACHA20 = 'chacha20';
    case CHACHA20_POLY1305 = 'chacha20-poly1305';
    case DES_CBC = 'des-cbc';
    case DES_CFB = 'des-cfb';
    case DES_CFB1 = 'des-cfb1';
    case DES_CFB8 = 'des-cfb8';
    case DES_ECB = 'des-ecb';
    case DES_EDE = 'des-ede';
    case DES_EDE_CBC = 'des-ede-cbc';
    case DES_EDE_CFB = 'des-ede-cfb';
    case DES_EDE_OFB = 'des-ede-ofb';
    case DES_EDE3 = 'des-ede3';
    case DES_EDE3_CBC = 'des-ede3-cbc';
    case DES_EDE3_CFB = 'des-ede3-cfb';
    case DES_EDE3_CFB1 = 'des-ede3-cfb1';
    case DES_EDE3_CFB8 = 'des-ede3-cfb8';
    case DES_EDE3_OFB = 'des-ede3-ofb';
    case DES_OFB = 'des-ofb';
    case DESX_CBC = 'desx-cbc';
    case ID_AES128_CCM = 'id-aes128-CCM';
    case ID_AES128_GCM = 'id-aes128-GCM';
    case ID_AES128_WRAP = 'id-aes128-wrap';
    case ID_AES128_WRAP_PAD = 'id-aes128-wrap-pad';
    case ID_AES192_CCM = 'id-aes192-CCM';
    case ID_AES192_GCM = 'id-aes192-GCM';
    case ID_AES192_WRAP = 'id-aes192-wrap';
    case ID_AES192_WRAP_PAD = 'id-aes192-wrap-pad';
    case ID_AES256_CCM = 'id-aes256-CCM';
    case ID_AES256_GCM = 'id-aes256-GCM';
    case ID_AES256_WRAP = 'id-aes256-wrap';
    case ID_AES256_WRAP_PAD = 'id-aes256-wrap-pad';
    case ID_SMIME_ALG_CMS3DESWRAP = 'id-smime-alg-CMS3DESwrap';
    case RC2_40_CBC = 'rc2-40-cbc';
    case RC2_64_CBC = 'rc2-64-cbc';
    case RC2_CBC = 'rc2-cbc';
    case RC2_CFB = 'rc2-cfb';
    case RC2_ECB = 'rc2-ecb';
    case RC2_OFB = 'rc2-ofb';
    case RC4 = 'rc4';
    case RC4_40 = 'rc4-40';
    case RC4_HMAC_MD5 = 'rc4-hmac-md5';
    case SEED_CBC = 'seed-cbc';
    case SEED_CFB = 'seed-cfb';
    case SEED_ECB = 'seed-ecb';
    case SEED_OFB = 'seed-ofb';
    case SM4_CBC = 'sm4-cbc';
    case SM4_CFB = 'sm4-cfb';
    case SM4_CTR = 'sm4-ctr';
    case SM4_ECB = 'sm4-ecb';
    case SM4_OFB = 'sm4-ofb';

    /**
     * @return string
     */
    public function generateIv(): string
    {
        return SecureRandom::generateBytes($this->getIvLength());
    }

    /**
     * @return int
     */
    public function getIvLength(): int
    {
        return openssl_cipher_iv_length($this->value);
    }

    /**
     * @return bool
     */
    public function isAead(): bool
    {
        return match ($this) {
            self::AES_128_GCM,
            self::AES_192_GCM,
            self::AES_256_GCM,
            self::CHACHA20_POLY1305,
            self::ARIA_128_GCM,
            self::ARIA_192_GCM,
            self::ARIA_256_GCM,
            self::ID_AES128_GCM,
            self::ID_AES192_GCM,
            self::ID_AES256_GCM,
            self::AES_128_CCM,
            self::AES_128_CCM_8 => true,
            default => false
        };
    }
}

