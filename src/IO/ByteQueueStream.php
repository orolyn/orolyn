<?php
namespace Orolyn\IO;

use Orolyn\ArgumentOutOfRangeException;

class ByteQueueStream implements IInputStream, IOutputStream
{
    use EndianTrait;
    use InputStreamTrait;
    use OutputStreamTrait;

    /**
     * @var Buffer
     */
    private Buffer $buffer;

    /**
     * @param string $bytes
     */
    public function __construct(string $bytes = '')
    {
        $this->buffer = new Buffer($bytes);
    }

    /**
     * @inheritdoc
     */
    public function getLength(): int
    {
        return $this->buffer->getLength();
    }

    /**
     * @inheritdoc
     */
    public function getPosition(): int
    {
        return 0;
    }

    /**
     * @inheritdoc
     */
    public function isEndOfStream(): bool
    {
        return 0 === $this->buffer->getLength();
    }

    /**
     * @inheritdoc
     */
    public function getBytesAvailable(): int
    {
        return $this->getLength();
    }

    /**
     * @inheritdoc
     */
    public function getBytesPending(): int
    {
        return 0;
    }

    /**
     * @inheritdoc
     */
    public function peek(int $length = 1): ?string
    {
        $position = $this->getPosition();
        $bytes = $this->read($length);
        $this->setPosition($position);

        return $bytes;
    }

    /**
     * @inheritdoc
     */
    public function read(int $length = 1): string
    {
        if ($length < 1) {
            throw new ArgumentOutOfRangeException();
        }

        if ($this->buffer->getLength() < $length) {
            throw new EndOfStreamException();
        }

        return $this->buffer->dequeue($length);
    }

    /**
     * @inheritdoc
     */
    public function write(string $bytes): void
    {
        $this->buffer->enqueue($bytes);
    }

    /**
     * @inheritdoc
     */
    public function setPosition(int $position): void
    {
        if (0 === $position) {
            return;
        }

        if ($position < 0) {
            throw new ArgumentOutOfRangeException('position');
        }

        $this->read($position);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->buffer;
    }
}
