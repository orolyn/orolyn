<?php

namespace Orolyn\Net\Security\TLS;

use Orolyn\Net\Security\TLS\Structure\CipherSuite;
use Orolyn\Net\Security\TLS\Structure\CipherSuiteList;
use Orolyn\Net\Security\TLS\Structure\ProtocolVersion;
use Orolyn\Net\Security\TLS\Structure\Random;

class Context
{
    public ?bool $isServer = null;
    public ?ProtocolVersion $protocolVersion = null;
    public ?Random $clientRandom = null;
    public ?Random $serverRandom = null;
    public ?CipherSuiteList $supportedCipherSuites = null;
    public ?CipherSuite $cipherSuite = null;
}
