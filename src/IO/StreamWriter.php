<?php
namespace Orolyn\IO;

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
