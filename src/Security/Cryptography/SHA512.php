<?php

namespace Orolyn\Security\Cryptography;

class SHA512 extends HashAlgorithm
{
    final public function __construct()
    {
        parent::__construct(HashAlgorithmMethod::SHA512);
    }
}
