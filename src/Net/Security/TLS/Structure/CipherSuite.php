<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\IEquatable;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;
use Orolyn\Security\Cryptography\CipherMethod;

/**
 * uint8 CipherSuite[2];
 */
enum CipherSuite: int implements IStructure, IEquatable
{
    // TLS 1.3
    case TLS_AES_128_GCM_SHA256                     = 0x1301;
    case TLS_AES_256_GCM_SHA384                     = 0x1302;
    case TLS_CHACHA20_POLY1305_SHA256               = 0x1303;
    case TLS_AES_128_CCM_SHA256                     = 0x1304;
    case TLS_AES_128_CCM_8_SHA256                   = 0x1305;

    /**
     * @inheritdoc
     */
    public function encode(IOutputStream $stream): void
    {
        $stream->writeUnsignedInt8($this->value >> 8);
        $stream->writeUnsignedInt8($this->value & 0xFF);
    }

    /**
     * @inheritdoc
     */
    public static function decode(IInputStream $stream, ?bool $server = null): static
    {
        $a = $stream->readUnsignedInt8();
        $b = $stream->readUnsignedInt8();

        return CipherSuite::from(($a << 8) | $b);
    }

    /**
     * @return CipherMethod
     */
    public function getCipherMethod(): CipherMethod
    {
        return match ($this) {
            self::TLS_AES_256_GCM_SHA384 => CipherMethod::AES_256_GCM,
            self::TLS_AES_128_GCM_SHA256 => CipherMethod::AES_128_GCM,
            self::TLS_CHACHA20_POLY1305_SHA256 => CipherMethod::CHACHA20_POLY1305,
            self::TLS_AES_128_CCM_SHA256 => CipherMethod::AES_128_CCM,
            self::TLS_AES_128_CCM_8_SHA256 => CipherMethod::AES_128_CCM_8,
        };
    }

    /**
     * @return string
     */
    public function getHashAlgorithm(): string
    {
        return match ($this) {
            self::TLS_AES_256_GCM_SHA384 => 'sha384',
            self::TLS_AES_128_GCM_SHA256,
            self::TLS_CHACHA20_POLY1305_SHA256,
            self::TLS_AES_128_CCM_SHA256,
            self::TLS_AES_128_CCM_8_SHA256 => 'sha256',
        };
    }

    /**
     * @inheritdoc
     */
    public function getHashCode(): int
    {
        return $this->value;
    }

    /**
     * @inheritdoc
     */
    public function equals(mixed $value): bool
    {
        if (!$value instanceof CipherSuite) {
            return false;
        }

        return $this->value === $value->value;
    }
}
