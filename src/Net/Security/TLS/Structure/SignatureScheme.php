<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;

/**
 *     enum {
 *     // RSASSA-PKCS1-v1_5 algorithms
 *     rsa_pkcs1_sha256(0x0401),
 *     rsa_pkcs1_sha384(0x0501),
 *     rsa_pkcs1_sha512(0x0601),
 *
 *     // ECDSA algorithms
 *     ecdsa_secp256r1_sha256(0x0403),
 *     ecdsa_secp384r1_sha384(0x0503),
 *     ecdsa_secp521r1_sha512(0x0603),
 *
 *     // RSASSA-PSS algorithms with public key OID rsaEncryption
 *     rsa_pss_rsae_sha256(0x0804),
 *     rsa_pss_rsae_sha384(0x0805),
 *     rsa_pss_rsae_sha512(0x0806),
 *
 *     // EdDSA algorithms
 *     ed25519(0x0807),
 *     ed448(0x0808),
 *
 *     // RSASSA-PSS algorithms with public key OID RSASSA-PSS
 *     rsa_pss_pss_sha256(0x0809),
 *     rsa_pss_pss_sha384(0x080a),
 *     rsa_pss_pss_sha512(0x080b),
 *
 *     // Legacy algorithms
 *     rsa_pkcs1_sha1(0x0201),
 *     ecdsa_sha1(0x0203),
 *
 *     // Reserved Code Points
 *     private_use(0xFE00..0xFFFF),
 *     (0xFFFF)
 * } SignatureScheme;
 */
enum SignatureScheme: int implements IStructure
{
    case RSA_PKCS1_SHA256 = 0x0401;
    case RSA_PKCS1_SHA384 = 0x0501;
    case RSA_PKCS1_SHA512 = 0x0601;

    /* ECDSA algorithms */
    case ECDSA_SECP256R1_SHA256 = 0x0403;
    case ECDSA_SECP384R1_SHA384 = 0x0503;
    case ECDSA_SECP521R1_SHA512 = 0x0603;

    /* RSASSA-PSS algorithms with public key OID rsaEncryption */
    case RSA_PSS_RSAE_SHA256 = 0x0804;
    case RSA_PSS_RSAE_SHA384 = 0x0805;
    case RSA_PSS_RSAE_SHA512 = 0x0806;

    /* EdDSA algorithms */
    case ED25519 = 0x0807;
    case ED448 = 0x0808;

    /* RSASSA-PSS algorithms with public key OID RSASSA-PSS */
    case RSA_PSS_PSS_SHA256 = 0x0809;
    case RSA_PSS_PSS_SHA384 = 0x080a;
    case RSA_PSS_PSS_SHA512 = 0x080b;

    /* Legacy algorithms */
    case RSA_PKCS1_SHA1 = 0x0201;
    case ECDSA_SHA1 = 0x0203;

    /**
     * @inheritdoc
     */
    public function encode(IOutputStream $stream): void
    {
        $stream->writeUnsignedInt16($this->value);
    }

    /**
     * @inheritdoc
     */
    public static function decode(IInputStream $stream, ?bool $server = null): static
    {
        return SignatureScheme::from($stream->readUnsignedInt16());
    }
}
