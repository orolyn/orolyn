<?php

namespace Orolyn\Net\Http\WebSocket;

use Orolyn\Collection\IList;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;
use Orolyn\Primitive\TypeUnsignedInt16;
use Orolyn\Net\Sockets\SocketNotConnectedException;
use Orolyn\SecureRandom;
use Orolyn\Timer;
use function Orolyn\Suspend;

class Frame
{
    public bool $final = false;
    public bool $rsv1 = false;
    public bool $rsv2 = false;
    public bool $rsv3 = false;
    public FrameOpcode $opcode = FrameOpcode::Continuation;
    public int $length = 0;
    public ?string $mask = null;
    public string $encoded = '';
    public string $decoded = '';

    /**
     * @param FrameOpcode $opcode
     * @param string $data
     * @param bool $isFinal
     * @param IList<Extension> $extensions
     * @return Frame
     */
    public static function create(FrameOpcode $opcode, string $data, bool $isFinal, IList $extensions): Frame
    {
        $frame = new Frame();
        $frame->final = $isFinal;
        $frame->opcode = $opcode;
        $frame->encoded = $frame->decoded = $data;

        foreach ($extensions as $extension) {
            $frame->encoded = $extension->encode($frame, $frame->encoded);
        }

        $frame->length = strlen($frame->encoded);
        $frame->mask = SecureRandom::generateBytes(4);
        $frame->encoded = Frame::maskPayload($frame->mask, $frame->length, $frame->encoded);

        return $frame;
    }

    /**
     * @param IInputStream $stream
     * @param IList<Extension> $extensions
     * @param float $timeout
     * @return false|Frame
     * @throws SocketNotConnectedException
     */
    public static function streamRecv(IInputStream $stream, IList $extensions, float $timeout = 0): false|Frame
    {
        $frame = new Frame();

        if ($timeout > 0) {
            $timer = new Timer($timeout);

            while ($stream->getBytesAvailable() < 1) {
                if ($timer->isExpired()) {
                    return false;
                }

                Suspend();
            }
        }

        $byte = $stream->readUnsignedInt8();

        $frame->final = ($byte & 0x80) >> 7;
        $frame->rsv1 = ($byte & 0x40) >> 6;
        $frame->rsv2 = ($byte & 0x20) >> 5;
        $frame->rsv3 = ($byte & 0x10) >> 4;

        $frame->opcode = FrameOpcode::getOpcode($byte & 0x0F);

        $byte = $stream->readUnsignedInt8();
        $hasMask = (bool)(($byte & 0x80) >> 7);

        $length = $byte & 0x7f;

        if (126 === $frame->length) {
            $length = $stream->readUnsignedInt16();
        } elseif (127 === $frame->length) {
            $length = $stream->readUnsignedInt64();
        }

        $frame->length = $length;

        if ($hasMask) {
            $frame->mask = $stream->read(4);
            $frame->encoded = self::maskPayload($frame->mask, $length, $stream->read($frame->length));
        } else {
            $frame->encoded = $stream->read($frame->length);
        }

        $frame->decoded = $frame->encoded;

        foreach ($extensions as $extension) {
            $frame->decoded = $extension->decode($frame, $frame->decoded);
        }

        return $frame;
    }

    /**
     * @param Frame $frame
     * @param IOutputStream $stream
     * @param IList<Extension> $extensions
     * @return void
     */
    public static function streamSend(Frame $frame, IOutputStream $stream): void
    {
        $stream->writeUnsignedInt8(
            ((int)$frame->final) << 7 |
            ((int)$frame->rsv1) << 6 |
            ((int)$frame->rsv2) << 5 |
            ((int)$frame->rsv3) << 4 |
            $frame->opcode->toInt()
        );

        $hasMask = (int)(null !== $frame->mask) << 7;

        if ($frame->length > TypeUnsignedInt16::MAX_VALUE) {
            $stream->writeUnsignedInt8($hasMask | 127);
            $stream->writeUnsignedInt64($frame->length);
        } elseif ($frame->length > 125) {
            $stream->writeUnsignedInt8($hasMask | 126);
            $stream->writeUnsignedInt16($frame->length);
        } else {
            $stream->writeUnsignedInt8($hasMask | $frame->length);
        }

        if (null !== $frame->mask) {
            $stream->write($frame->mask);
        }

        $stream->write($frame->encoded);
    }

    /**
     * @param string $mask
     * @param int $length
     * @param string $payload
     * @return string
     */
    private static function maskPayload(string $mask, int $length, string $payload): string
    {
        $output = '';

        for ($i = 0; $i < $length; $i += 4) {
            $output .= substr($payload, $i, 4) ^ $mask;
        }

        return $output;
    }
}
