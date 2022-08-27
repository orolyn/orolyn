<?php

namespace Orolyn\Security\Cryptography;

class HMACMD5 extends HMAC
{
    final public function __construct(string $key)
    {
        parent::__construct(HashAlgorithmMethod::MD5, $key);
    }
}
