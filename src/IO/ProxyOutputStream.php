<?php
namespace Orolyn\IO;

use Orolyn\ArgumentOutOfRangeException;

class ProxyOutputStream implements IOutputStream
{
    use EndianTrait;
    use OutputStreamTrait;

    /**
     * @param IOutputStream $innerStream
     */
    public function __construct(
        public readonly IOutputStream $innerStream
    ) {
    }

    /**
     * @inheritdoc
     */
    public function setPosition(int $position): void
    {
        $this->innerStream->setPosition($position);
    }

    /**
     * @inheritdoc
     */
    public function getPosition(): int
    {
        return $this->innerStream->getPosition();
    }

    /**
     * @inheritdoc
     */
    public function getBytesPending(): int
    {
        return $this->innerStream->getBytesPending();
    }

    /**
     * @inheritdoc
     */
    public function write(string $bytes): void
    {
        $this->innerStream->write($bytes);
    }

    /**
     * inheritdoc
     */
    public function flush(): void
    {
        $this->innerStream->flush();
    }
}
