<?php

namespace Orolyn\Security\Cryptography;

use Orolyn\InvalidOperationException;
use Orolyn\IO\IInputStream;

abstract class KeyedHashAlgorithm extends HashAlgorithm
{
    /**
     * @param HashAlgorithmMethod $algorithmMethod
     * @param string $key
     */
    protected function __construct(
        HashAlgorithmMethod $algorithmMethod,
        public string $key
    ) {
        parent::__construct($algorithmMethod);
    }
}
