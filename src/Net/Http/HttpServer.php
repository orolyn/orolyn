<?php

namespace Orolyn\Net\Http;

use Orolyn\Endian;
use Orolyn\FormatException;
use Orolyn\IO\ByteStream;
use Orolyn\Net\Http\Parser\Parser;
use Orolyn\Net\ServerEndPoint;
use Orolyn\Net\Sockets\ServerSocket;

class HttpServer
{
    /**
     * @var ServerSocket|null
     */
    protected ?ServerSocket $server = null;

    /**
     * @return bool
     */
    public function isListening(): bool
    {
        return $this?->server->isListening();
    }

    /**
     * @return void
     */
    public function listen(ServerEndPoint $endPoint): void
    {
        $this->server = new ServerSocket();
        $this->server->listen($endPoint);
    }

    /**
     * @return HttpRequestContext|null
     */
    public function accept(): ?HttpRequestContext
    {
        if (null === $this->server || null === $socket = $this->server->accept()) {
            return null;
        }

        $socket->setEndian(Endian::BigEndian);

        try {
            $line = Parser::parseRequestLine($socket);
            $headers = [];

            while (null !== $header = Parser::parseHeader($socket)) {
                $headers[$header->getName()] = $header;
            }
        } catch (FormatException $exception) {
            $socket->close();

            return null;
        }

        $request = HttpRequest::create($line, $headers);

        if (-1 !== $contentLength = $request->getContentLength()) {
            $request->setBody(new ByteStream($socket->read($contentLength)));
        }

        return new HttpRequestContext(
            $request,
            $socket
        );
    }

    public function close(): void
    {
        $this->server?->close();
    }
}
