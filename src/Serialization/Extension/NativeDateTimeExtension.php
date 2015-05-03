<?php
namespace Orolyn\Serialization\Extension;

use Orolyn\Delegate;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;
use function Orolyn\Lang\String;
use Orolyn\Serialization\IExtension;

class NativeDateTimeExtension implements IExtension
{
    /**
     * @param IOutputStream $stream
     * @param \DateTime $value
     * @param Delegate $serialize
     * @return \Generator
     */
    public function serialize(IOutputStream $stream, $value, Delegate $serialize): \Generator
    {
        $parts = explode(' ', $value->format('Y m d H i s u'));

        $stream->writeInt16((int)$parts[0]);
        $stream->writeInt8((int)$parts[1]);
        $stream->writeInt8((int)$parts[2]);
        $stream->writeInt8((int)$parts[3]);
        $stream->writeInt8((int)$parts[4]);
        $stream->writeInt8((int)$parts[5]);
        $stream->writeInt32((int)$parts[6]);

        yield;
    }

    public function deserialize(IInputStream $stream, &$value, Delegate $deserialize, Delegate $waitBytes, Delegate $addReference)
    {
        // 2019-08-07 18:35:03.437741
        // 16   8  8  8  8  8  32

        yield from $waitBytes->call(2 + 1 + 1 + 1 + 1 + 1 + 4);

        $value = new \DateTime(
            String('%s-%s-%s %s:%s:%s.%s')->format(
                $stream->readInt16(),
                String($stream->readInt8())->padLeft(2, '0'),
                String($stream->readInt8())->padLeft(2, '0'),
                String($stream->readInt8())->padLeft(2, '0'),
                String($stream->readInt8())->padLeft(2, '0'),
                String($stream->readInt8())->padLeft(2, '0'),
                String($stream->readInt32())->padLeft(6, '0')
            )
        );

        $addReference->call($value);
    }

    public function getType(): string
    {
        return \DateTime::class;
    }
}
