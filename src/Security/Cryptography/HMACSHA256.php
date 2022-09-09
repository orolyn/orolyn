<?php

namespace Orolyn\Security\Cryptography;

class HMACSHA256 extends HMAC
{
    final public function __construct(string $key)
    {
        parent::__construct(HashAlgorithmMethod::SHA256, $key);
    }
}
