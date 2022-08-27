<?php

namespace Orolyn\Security\Cryptography;

class RIPEMD160 extends HashAlgorithm
{
    final public function __construct()
    {
        parent::__construct(HashAlgorithmMethod::RIPEMD160);
    }
}
