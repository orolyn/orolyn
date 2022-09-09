<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\Net\Security\TLS\Context;
use Orolyn\Endian;
use Orolyn\IO\ByteStream;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;

abstract class Structure implements IStructure
{
    protected ?string $contentCache = null;

    /**
     * @param string|IStructure|null $data
     * @return ByteStream
     */
    public static function createByteStream(
        string|IStructure|null $data = null
    ): ByteStream {
        $byteStream = new ByteStream();
        $byteStream->setEndian(Endian::BigEndian);

        if (null !== $data) {
            if ($data instanceof IStructure) {
                $data->encode($byteStream);
            } else {
                $byteStream->write($data);
            }

            $byteStream->reset();
        }

        return $byteStream;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if (null === $this->contentCache) {
            $stream = self::createByteStream();
            $this->encode($stream);

            return (string)$stream;
        }

        return $this->contentCache;
    }
}
