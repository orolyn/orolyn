<?php
namespace Orolyn\IO;

use Orolyn\ArgumentOutOfRangeException;

class ProxyInputStream implements IInputStream
{
    use EndianTrait;
    use InputStreamTrait;

    /**
     * @param IInputStream $innerStream
     */
    public function __construct(
        public readonly IInputStream $innerStream
    ) {
    }

    /**
     * @inheritDoc
     */
    public function setPosition(int $position): void
    {
        $this->innerStream->setPosition($position);
    }

    /**
     * @inheritDoc
     */
    public function getPosition(): int
    {
        return $this->innerStream->getPosition();
    }

    /**
     * @inheritDoc
     */
    public function isEndOfStream(): bool
    {
        return $this->innerStream->isEndOfStream();
    }

    /**
     * @inheritDoc
     */
    public function getLength(): int
    {
        return $this->innerStream->getLength();
    }

    /**
     * @inheritDoc
     */
    public function getBytesAvailable(): int
    {
        return $this->innerStream->getBytesAvailable();
    }

    /**
     * @inheritDoc
     */
    public function peek(int $length = 1): ?string
    {
        return $this->innerStream->peek($length);
    }

    /**
     * @inheritDoc
     */
    public function read(int $length = 1): string
    {
        return $this->innerStream->read($length);
    }
}
