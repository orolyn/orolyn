<?php
namespace Orolyn\IO;

use Closure;

class StreamWriter
{
    public function __construct(
        private IOutputStream $stream
    ) {
    }

    public function writeLine(string $line): void
    {
        $this->stream->write($line);
        $this->stream->write("\r\n");
    }
}
