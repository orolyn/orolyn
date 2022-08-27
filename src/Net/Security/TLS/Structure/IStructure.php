<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\IO\ByteStream;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;

interface IStructure
{
    /**
     * @param IOutputStream $stream
     * @return void
     */
    public function encode(IOutputStream $stream): void;

    /**
     * @param IInputStream $stream
     * @param bool|null $server
     * @return static
     */
    public static function decode(IInputStream $stream, ?bool $server = null): static;

}
