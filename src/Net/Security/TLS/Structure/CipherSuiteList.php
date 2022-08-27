<?php

namespace Orolyn\Net\Security\TLS\Structure;

/**
 * CipherSuite cipher_suites<2..2^16-2>;
 *
 * @extends VariableLengthVector<CipherSuite>
 */
class CipherSuiteList extends VariableLengthVector
{
    protected static string $structureClass = CipherSuite::class;
    protected static VariableLength $variableLength = VariableLength::UInt16;

    /**
     * @return CipherSuiteList
     */
    public static function getModernCipherSuiteList(): CipherSuiteList
    {
        return new CipherSuiteList(
            [
                CipherSuite::TLS_AES_128_GCM_SHA256,
                CipherSuite::TLS_AES_256_GCM_SHA384,
                CipherSuite::TLS_CHACHA20_POLY1305_SHA256
            ]
        );
    }
}
