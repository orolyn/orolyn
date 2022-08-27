<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\IEquatable;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;

/**
 * enum {
 *     // Elliptic Curve Groups (ECDHE)
 *     secp256r1(0x0017),
 *     secp384r1(0x0018),
 *     secp521r1(0x0019),
 *     x25519(0x001D),
 *     x448(0x001E),
 *
 *     // Finite Field Groups (DHE)
 *     ffdhe2048(0x0100),
 *     ffdhe3072(0x0101),
 *     ffdhe4096(0x0102),
 *     ffdhe6144(0x0103),
 *     ffdhe8192(0x0104),
 *
 *     // Reserved Code Points
 *     ffdhe_private_use(0x01FC..0x01FF),
 *     ecdhe_private_use(0xFE00..0xFEFF),
 *     (0xFFFF)
 * } NamedGroup;
 */
enum NamedGroup: int implements IStructure, IEquatable
{
    case X25519        = 0x001D;
    case SECP256R1    = 0x0017;
    case X448          = 0x001E;
    case SECP521R1    = 0x0019;
    case SECP384R1    = 0x0018;

    case FFDHE2048     = 0x0100;
    case FFDHE3072     = 0x0101;
    case FFDHE4096     = 0x0102;
    case FFDHE6144     = 0x0103;
    case FFDHE8192     = 0x0104;

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
        return NamedGroup::from($stream->readUnsignedInt16());
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
        if (!$value instanceof NamedGroup) {
            return false;
        }

        return $this->value === $value->value;
    }
}
