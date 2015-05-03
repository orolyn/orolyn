<?php
namespace Orolyn\Serialization;

use Orolyn\Delegate;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;

interface IExtension
{
    public function serialize(IOutputStream $stream, $value, Delegate $serialize);

    public function deserialize(IInputStream $stream, &$value, Delegate $deserialize, Delegate $waitBytes, Delegate $addReference);

    public function getType(): string;
}
