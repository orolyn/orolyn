<?php

namespace Orolyn\Security\Cryptography;

class SHA1 extends HashAlgorithm
{
    final public function __construct()
    {
        parent::__construct(HashAlgorithmMethod::SHA1);
    }
}
