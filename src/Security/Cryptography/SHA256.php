<?php

namespace Orolyn\Security\Cryptography;

class SHA256 extends HashAlgorithm
{
    final public function __construct()
    {
        parent::__construct(HashAlgorithmMethod::SHA256);
    }
}
