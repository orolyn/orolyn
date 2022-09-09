<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\Net\Security\TLS\Context;
use Orolyn\IO\ByteStream;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;

/**
 * ProtocolVersion versions<2..254>;
 *
 * @extends VariableLengthVector<NamedGroup>
 */
class NamedGroupVector extends VariableLengthVector
{
    protected static string $structureClass = NamedGroup::class;
    protected static VariableLength $variableLength = VariableLength::UInt16;

    public static function getRecommended(): NamedGroupVector
    {
        return new NamedGroupVector(
            [
                NamedGroup::X25519,
                NamedGroup::SECP256R1,
                NamedGroup::X448,
                NamedGroup::SECP521R1,
                NamedGroup::SECP384R1,
                NamedGroup::FFDHE2048,
                NamedGroup::FFDHE3072,
                NamedGroup::FFDHE4096,
                NamedGroup::FFDHE6144,
                NamedGroup::FFDHE8192
            ]
        );
    }
}
