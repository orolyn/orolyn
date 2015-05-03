<?php

namespace Orolyn\Net\Http;

use Orolyn\Net\ServerEndPoint;
use Orolyn\Net\Sockets\SecureServerSocket;
use Orolyn\Net\Sockets\SocketContext;

class SecureHttpServer extends HttpServer
{
    private SocketContext $context;

    public function __construct(?SocketContext $context = null)
    {
        $this->context = $context ?? new SocketContext();
    }

    public function listen(ServerEndPoint $endPoint): void
    {
        $this->server = new SecureServerSocket($this->context);
        $this->server->listen($endPoint);
    }
}
