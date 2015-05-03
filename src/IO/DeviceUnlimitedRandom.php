<?php

namespace Orolyn\IO;

class DeviceUnlimitedRandom implements IInputStream
{
    use EndianTrait;
    use InputStreamTrait;

    public function __construct()
    {

    }

    public function setPosition(int $position): void
    {
        // TODO: Implement setPosition() method.
    }

    public function getPosition(): int
    {
        // TODO: Implement getPosition() method.
    }

    public function isEndOfStream(): bool
    {
        // TODO: Implement isEndOfStream() method.
    }

    public function getLength(): int
    {
        // TODO: Implement getLength() method.
    }

    public function getBytesAvailable(): int
    {
        // TODO: Implement getBytesAvailable() method.
    }

    public function peek(int $length = 1): ?string
    {
        // TODO: Implement peek() method.
    }
}
