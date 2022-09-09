<?php

namespace Orolyn\Security\Cryptography;

class MD5 extends HashAlgorithm
{
    final public function __construct()
    {
        parent::__construct(HashAlgorithmMethod::MD5);
    }
}
