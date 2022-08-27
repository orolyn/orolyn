<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;

/**
 * @extends VariableLengthVector<ServerName>
 */
class ServerNameVector extends VariableLengthVector
{
    protected static string $structureClass = ServerName::class;
    protected static VariableLength $variableLength = VariableLength::UInt16;
}
