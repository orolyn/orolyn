<?php

namespace Orolyn\Net\Http;

use Orolyn\InvalidOperationException;
use Orolyn\IO\StreamWriter;
use Orolyn\Net\Sockets\Socket;
use Orolyn\Net\Sockets\SocketNotConnectedException;

class HttpRequestContext
{
    private bool $sent = false;

    /**
     * @param HttpRequest $request
     * @param Socket $socket
     */
    public function __construct(
        private HttpRequest $request,
        private Socket $socket
    ) {
    }

    /**
     * @return HttpRequest
     */
    public function getRequest(): HttpRequest
    {
        return $this->request;
    }

    /**
     * @return Socket
     */
    public function getConnection(): Socket
    {
        return $this->socket;
    }

    /**
     * @param HttpResponse $response
     * @return void
     * @throws InvalidOperationException
     * @throws SocketNotConnectedException
     */
    public function send(HttpResponse $response): void
    {
        if ($this->sent) {
            throw new InvalidOperationException('Response already sent.');
        }

        $writer = new StreamWriter($this->socket);

        $reasonPhrase = $response->getReasonPhrase();

        $writer->writeLine(
            sprintf(
                'HTTP/%s %s%s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $reasonPhrase ? ' ' . $reasonPhrase : ''
            )
        );

        foreach ($response->getHeaders() as $header) {
            foreach ($header as $value) {
                $writer->writeLine(
                    sprintf(
                        '%s: %s',
                        Header::cannonicalizeName($header->getName()),
                        $value
                    )
                );
            }
        }

        $writer->writeLine('');

        if ($body = $response->getBody()) {
            while (null !== $bytes = $body->read(1024 << 3)) {
                $this->socket->write($bytes);
                $this->socket->flush();
            }
        }

        $this->socket->flush();

        $this->sent = true;
    }

    public function terminate(): void
    {
        $this->socket->close();
    }
}
