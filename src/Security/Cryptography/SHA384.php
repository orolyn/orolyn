<?php

namespace Orolyn\Security\Cryptography;

class SHA384 extends HashAlgorithm
{
    final public function __construct()
    {
        parent::__construct(HashAlgorithmMethod::SHA384);
    }
}
