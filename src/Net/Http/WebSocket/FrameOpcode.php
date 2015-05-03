<?php

namespace Orolyn\Net\Http\WebSocket;

use Orolyn\ArgumentException;

enum FrameOpcode
{
    case Continuation;
    case Text;
    case Binary;
    case ConnectionClose;
    case Ping;
    case Pong;

    /**
     * @param int $value
     * @return FrameOpcode
     */
    public static function getOpcode(int $value): FrameOpcode
    {
        $opcode = match($value) {
            0x0 => self::Continuation,
            0x1 => self::Text,
            0x2 => self::Binary,
            0x8 => self::ConnectionClose,
            0x9 => self::Ping,
            0xA => self::Pong,
            default => null
        };

        if ($opcode) {
            return $opcode;
        }

        throw new ArgumentException('Invalid opcode.');
    }

    public function toInt(): int
    {
        return match ($this) {
            self::Continuation => 0x0,
            self::Text => 0x1,
            self::Binary => 0x2,
            self::ConnectionClose => 0x8,
            self::Ping => 0x9,
            self::Pong => 0xA
        };
    }

    public function isControl(): bool
    {
        return in_array(
            $this,
            [
                self::ConnectionClose,
                self::Ping,
                self::Pong
            ]
        );
    }
}
