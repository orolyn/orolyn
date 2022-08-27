<?php

namespace Orolyn\Security\Cryptography;

class HMACRIPEMD160 extends HMAC
{
    final public function __construct(string $key)
    {
        parent::__construct(HashAlgorithmMethod::RIPEMD160, $key);
    }
}
