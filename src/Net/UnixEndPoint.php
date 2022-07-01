<?php
namespace Orolyn\Net;

use function Orolyn\Int32;
use function Orolyn\String;

final class UnixEndPoint extends EndPoint implements ServerEndPoint
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function __toString(): string
    {
        return $this->path;
    }
}
