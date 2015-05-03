<?php

namespace Orolyn\Net\Http\Parser;

class RequestLine
{
    /**
     * @param string $method
     * @param string $path
     * @param string $version
     */
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly string $version,
    ) {
    }
}
