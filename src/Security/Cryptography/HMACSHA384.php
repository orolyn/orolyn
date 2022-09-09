<?php

namespace Orolyn\Security\Cryptography;

class HMACSHA384 extends HMAC
{
    final public function __construct(string $key)
    {
        parent::__construct(HashAlgorithmMethod::SHA384, $key);
    }
}
