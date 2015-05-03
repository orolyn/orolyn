<?php
namespace Orolyn\IO;

class StreamReader
{
    public function __construct(
        private IInputStream $stream
    ) {
    }

    public function readLine(): ?string
    {
        $bytes = '';

        for ($i = 0;;$i++) {
            $char = $this->stream->read(1);

            if ('' === $char) {
                if (0 === $i) {
                    return null;
                }

                return $bytes;
            }

            if ("\n" === $char) {
                return $bytes;
            }

            if ("\r" === $char) {
                if ("\n" === $this->stream->peek()) {
                    $this->stream->read(1);
                }

                return $bytes;
            }

            $bytes .= $char;
        }
    }
}
