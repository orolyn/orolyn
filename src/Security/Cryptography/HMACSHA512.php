<?php

namespace Orolyn\Security\Cryptography;

class HMACSHA512 extends HMAC
{
    final public function __construct(string $key)
    {
        parent::__construct(HashAlgorithmMethod::SHA512, $key);
    }
}
