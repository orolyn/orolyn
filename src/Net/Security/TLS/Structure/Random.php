<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\ArgumentException;
use Orolyn\ByteConverter;
use Orolyn\IEquatable;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;

/**
 * opaque random_bytes[32];
 */
class Random extends Structure implements IEquatable
{
    public function __construct(
        public readonly string $randomBytes
    ) {
        if (strlen($this->randomBytes) < 32) {
            throw new ArgumentException('Random bytes must be at least 32 in length');
        }
    }

    /**
     * @inheritdoc
     */
    public function encode(IOutputStream $stream): void
    {
        $stream->write(substr($this->randomBytes, 0, 32));
    }

    /**
     * @inheritdoc
     */
    public static function decode(IInputStream $stream, ?bool $server = null): static
    {
        return new Random($stream->read(32));
    }

    /**
     * @inheritdoc
     */
    public function equals(mixed $value): bool
    {
        if (!$value instanceof Random) {
            return false;
        }

        return $this->randomBytes === $value->randomBytes;
    }

    /**
     * @inheritdoc
     */
    public function getHashCode(): int
    {
        return
            ByteConverter::getInt32(
                substr($this->randomBytes, 0, 4) ^
                substr($this->randomBytes, 4, 4) ^
                substr($this->randomBytes, 8, 4) ^
                substr($this->randomBytes, 12, 4) ^
                substr($this->randomBytes, 16, 4) ^
                substr($this->randomBytes, 20, 4) ^
                substr($this->randomBytes, 24, 4) ^
                substr($this->randomBytes, 28, 4)
            );
    }
}
