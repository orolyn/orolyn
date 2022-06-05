<?php

namespace Orolyn\Net\Http\WebSocket;

use Orolyn\Collection\ArrayList;
use Orolyn\Collection\EmptyList;
use Orolyn\Collection\IList;
use Orolyn\Collection\OrderedSet;
use Orolyn\Collection\Queue;
use Orolyn\Collection\StaticList;
use Orolyn\Concurrency\TaskLock;
use Orolyn\Endian;
use Orolyn\IO\IInputStream;
use Orolyn\Net\Http\HttpRequestContext;
use Orolyn\Net\Http\HttpResponse;
use Orolyn\Net\Sockets\Socket;
use Orolyn\Net\Sockets\SocketNotConnectedException;
use Orolyn\Timer;
use function Orolyn\Lang\Lock;
use function Orolyn\Lang\Suspend;
use function Orolyn\Lang\Unlock;
use function Orolyn\Lang\UnsignedInt16;

class WebSocket
{
    /**
     * @var OrderedSet<Frame>
     */
    private OrderedSet $frames;
    private ?TaskLock $receiveLock = null;
    private ?TaskLock $pingLock = null;

    /**
     * @param Socket $socket
     * @param IList<Extension> $extensions
     */
    public function __construct(
        private Socket $socket,
        private IList $extensions
    ) {
        $this->frames = new OrderedSet();
    }

    /**
     * @param HttpRequestContext $context
     * @param IList|null $supportedExtensions
     * @return WebSocket
     * @throws InvalidWebSocketContextException
     */
    public static function create(HttpRequestContext $context, ?IList $supportedExtensions = null): WebSocket
    {
        $request = $context->getRequest();

        if (strtolower($request->getHeader('Upgrade')->getValue()) !== 'websocket') {
            throw new InvalidWebSocketContextException('Request is not a websocket upgrade');
        }

        $supportedExtensions = $supportedExtensions ?? StaticList::createImmutableList([PermessageDeflate::class]);

        $extensions = Extension::createExtensions(
            $request->getHeader('Sec-WebSocket-Extensions'),
            $supportedExtensions
        );

        $response = new HttpResponse(null, 101, [], '1.1');
        $response->setHeader('Upgrade', 'websocket');
        $response->setHeader('Connection', 'Upgrade');

        if ($extensions->count() > 0) {
            $response->addHeader(Extension::createHeader($extensions));
        }

        $response->setHeader(
            'Sec-WebSocket-Accept',
            base64_encode(
                sha1(
                    $request->getHeader('Sec-WebSocket-Key') . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
                    true
                )
            )
        );

        try {
            $context->send($response);
        } catch (SocketNotConnectedException $exception) {
            throw new InvalidWebSocketContextException(
                'Internal socket closed before finalising the connection',
                0,
                $exception
            );
        }

        return new WebSocket($context->getConnection(), $extensions);
    }

    /**
     * @return void
     */
    public function close(): void
    {
        $this->socket->close();
    }

    /**
     * Return true if the connection is closed.
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->socket->isConnected();
    }

    /**
     * Returns true if both the connection is closed and there are no more available frames.
     *
     * @return bool
     */
    public function isExhausted(): bool
    {
        return !($this->isConnected() || $this->frames->count() > 0 || $this->socket->getBytesAvailable() > 0);
    }

    /**
     * @param string|WebSocketMessage $message
     * @param bool $utf8
     * @return void
     * @throws WebSocketClosedException
     */
    public function send(string|WebSocketMessage $message, bool $utf8 = true): void
    {
        if ($message instanceof WebSocketMessage) {
            $utf8 = $message->isUtf8();
            $message = $message->getData();
        }

        $this->sendFrame(
            $message,
            $utf8 ? FrameOpcode::Text : FrameOpcode::Binary,
            true
        );
    }

    /**
     * @return WebSocketMessage
     * @throws WebSocketClosedException
     */
    public function receive(): WebSocketMessage
    {
        return Lock($this->receiveLock, function () {
            $content = '';
            $utf8 = null;

            for (;;) {
                $frame = $this->getFrame(
                    [
                        FrameOpcode::Continuation,
                        FrameOpcode::Text,
                        FrameOpcode::Binary
                    ]
                );

                if (null === $utf8) {
                    $utf8 = match ($frame->opcode) {
                        FrameOpcode::Text => true,
                        FrameOpcode::Binary => false,
                        default => null
                    };
                }

                $content .= $frame->decoded;

                if ($frame->final) {
                    break;
                }
            }

            return new WebSocketMessage(
                $content,
                $utf8
            );
        });
    }

    /**
     * @param string $data
     * @param float $timeout
     * @return bool
     * @throws WebSocketClosedException
     */
    public function ping(string $data, float $timeout = 0): bool
    {
        return Lock($this->pingLock, function () use ($data, $timeout) {
            $this->sendFrame($data, FrameOpcode::Ping, true);

            $remaining = $timeout;
            $timer = $timeout > 0 ? new Timer($timeout) : null;

            for (;;) {
                if ($timer) {
                    if ($timer->isExpired()) {
                        return false;
                    }

                    $remaining = $timer->getRemaining();
                }

                $frame = $this->getFrame([FrameOpcode::Pong], $remaining);

                if ($frame && $frame->decoded === $data) {
                    return true;
                }
            }
        });
    }

    public function pong(string $data): void
    {
        $this->sendFrame($data, FrameOpcode::Pong, true);
    }

    private function sendFrame(string $message, FrameOpcode $opcode, bool $isFinal)
    {
        $frame = Frame::create($opcode, $message, $isFinal, $this->extensions);
        Frame::streamSend($frame, $this->socket);

        try {
            $this->socket->flush();
        } catch (SocketNotConnectedException $exception) {
            throw new WebSocketClosedException();
        }
    }

    /**
     * @param array $opcodes
     * @param float $timeout
     * @return false|Frame
     * @throws WebSocketClosedException
     */
    private function getFrame(array $opcodes, float $timeout = 0): false|Frame
    {
        foreach ($this->frames as $frame) {
            if (in_array($frame->opcode, $opcodes)) {
                $this->frames->remove($frame);

                return $frame;
            }
        }

        try {
            for (;;) {
                $frame = Frame::streamRecv($this->socket, $this->extensions, $timeout);

                if (!$frame) {
                    return false;
                }

                if (FrameOpcode::ConnectionClose === $frame->opcode) {
                    throw new WebSocketClosedException(
                        'Websocket has been closed by the remote endpoint',
                        2 === $frame->length ? UnsignedInt16($frame->decoded, Endian::BigEndian)->getValue() : 0
                    );
                }

                if (FrameOpcode::Ping === $frame->opcode) {
                    $this->pong($frame->decoded);
                    continue;
                }

                if (in_array($frame->opcode, $opcodes)) {
                    return $frame;
                }

                $this->frames->add($frame);
            }
        } catch (SocketNotConnectedException $exception) {
            throw new WebSocketClosedException('Websocket closed unexpectedly.');
        }
    }
}
