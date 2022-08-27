<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\IO\ByteStream;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;

/**
 * ProtocolVersion versions<2..254>;
 *
 * @extends VariableLengthVector<ProtocolVersion>
 */
class ProtocolVersionVector extends VariableLengthVector
{
    protected static string $structureClass = ProtocolVersion::class;
    protected static VariableLength $variableLength = VariableLength::UInt8;
}
