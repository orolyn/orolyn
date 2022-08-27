<?php

namespace Orolyn\Security\Cryptography;

class HMACSHA1 extends HMAC
{
    final public function __construct(string $key)
    {
        parent::__construct(HashAlgorithmMethod::SHA1, $key);
    }
}
