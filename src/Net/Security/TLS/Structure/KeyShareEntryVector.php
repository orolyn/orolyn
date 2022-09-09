<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\Net\Security\TLS\Context;
use Orolyn\IO\ByteStream;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;

/**
 * ProtocolVersion versions<2..254>;
 *
 * @extends VariableLengthVector<KeyShareEntry>
 */
class KeyShareEntryVector extends VariableLengthVector
{
    protected static string $structureClass = KeyShareEntry::class;
    protected static VariableLength $variableLength = VariableLength::UInt16;
}
