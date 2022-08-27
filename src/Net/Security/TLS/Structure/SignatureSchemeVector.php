<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\Net\Security\TLS\Context;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;

/**
 * @extends VariableLengthVector<SignatureScheme>
 */
class SignatureSchemeVector extends VariableLengthVector
{
    protected static string $structureClass = SignatureScheme::class;
    protected static VariableLength $variableLength = VariableLength::UInt16;
}
