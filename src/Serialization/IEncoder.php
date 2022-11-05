<?php

namespace Orolyn\Serialization;

use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;

interface IEncoder
{
    /**
     * @param IInputStream $stream
     * @param class-string<IEncodable> $className
     * @return IEncodable
     */
    public function decode(IInputStream $stream, string $className): IEncodable;

    /**
     * @param IOutputStream $stream
     * @param IEncodable $object
     * @return void
     */
    public function encode(IOutputStream $stream, IEncodable $object): void;
}
