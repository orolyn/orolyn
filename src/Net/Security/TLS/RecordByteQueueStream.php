<?php

namespace Orolyn\Net\Security\TLS;

use Orolyn\IO\ByteQueueStream;
use function Orolyn\Suspend;

class RecordByteQueueStream extends ByteQueueStream
{
    /**
     * @inheritdoc
     */
    public function read(int $length = 1): string
    {
        while ($this->getBytesAvailable() < $length) {
            Suspend();
        }

        return parent::read($length);
    }
}
