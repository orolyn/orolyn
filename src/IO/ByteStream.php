<?php
namespace Orolyn\IO;

use Orolyn\ArgumentOutOfRangeException;

class ByteStream implements IInputStream, IOutputStream
{
    use EndianTrait;
    use InputStreamTrait;
    use OutputStreamTrait;

    private $bytes;

    private $position = 0;

    public function __construct(string $bytes = '')
    {
        $this->bytes = $bytes;
    }

    public function getLength(): int
    {
        return strlen($this->bytes);
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function isEndOfStream(): bool
    {
        return $this->position >= strlen($this->bytes);
    }

    public function getBytesAvailable(): int
    {
        return $this->getLength() - $this->position;
    }

    public function getBytesPending(): int
    {
        return 0;
    }

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
        if ($length < 0) {
            throw new ArgumentOutOfRangeException('length');
        }

        if ($this->getBytesAvailable() < $length) {
            throw new EndOfStreamException();
        }

        $position = $this->position;
        $this->position += $length;

        return substr($this->bytes, $position, $length);
    }

    public function write(string $bytes): void
    {
        $length = strlen($bytes);
        $this->bytes =
            substr($this->bytes, 0, $this->position) . $bytes . substr($this->bytes, $this->position + $length);
        $this->position += $length;
    }

    public function setPosition(int $position): void
    {
        if ($position < 0) {
            throw new ArgumentOutOfRangeException('position');
        }

        $this->position = $position;
    }

    public function reset(): void
    {
        $this->setPosition(0);;
    }

    public function __toString(): string
    {
        return $this->bytes;
    }
}
