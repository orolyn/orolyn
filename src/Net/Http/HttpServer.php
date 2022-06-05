<?php

namespace Orolyn\Net\Http;

use Orolyn\Endian;
use Orolyn\FormatException;
use Orolyn\InvalidOperationException;
use Orolyn\IO\ByteStream;
use Orolyn\Net\Http\Parser\Parser;
use Orolyn\Net\ServerEndPoint;
use Orolyn\Net\Sockets\ServerSocket;
use Orolyn\Net\Sockets\SocketNotConnectedException;

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
     * @throws FailedHttpRequestException
     */
    public function accept(): ?HttpRequestContext
    {
        if (null === $this->server) {
            throw new InvalidOperationException('Http server is not configured for listening');
        }

        $socket = $this->server->accept();
        $socket->setEndian(Endian::BigEndian);

        try {
            try {
                $line = Parser::parseRequestLine($socket);
                $headers = [];

                while (null !== $header = Parser::parseHeader($socket)) {
                    $headers[$header->getName()] = $header;
                }
            } catch (FormatException $exception) {
                $socket->close();

                throw new FailedHttpRequestException('Could not parse request headers', 0, $exception);
            }

            $request = HttpRequest::create($line, $headers);

            if (-1 !== $contentLength = $request->getContentLength()) {
                $request->setBody(new ByteStream($socket->read($contentLength)));
            }
        } catch (SocketNotConnectedException $exception) {
            throw new FailedHttpRequestException('Connection closed before completing the request');
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
