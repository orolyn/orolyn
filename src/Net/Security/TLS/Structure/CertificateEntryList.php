<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\Net\Security\TLS\Context;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;

/**
 * CertificateEntry certificate_list<0..2^24-1>;
 *
 * @extends VariableLengthVector<CertificateEntry>
 */
class CertificateEntryList extends VariableLengthVector
{
    protected static string $structureClass = CertificateEntry::class;
    protected static VariableLength $variableLength = VariableLength::UInt24;
}
