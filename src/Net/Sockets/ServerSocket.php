<?php
namespace Orolyn\Net\Sockets;

use Exception;
use Orolyn\InvalidOperationException;
use Orolyn\IO\File;
use Orolyn\Net\IPEndPoint;
use Orolyn\Net\ServerEndPoint;
use Orolyn\Net\Sockets\Options\SecureOptions;
use Orolyn\Net\Sockets\Options\ServerSocketOptions;
use Orolyn\Net\Sockets\Options\SocketOptions;
use Orolyn\Net\UnixEndPoint;
use Orolyn\Reflection;
use function Orolyn\Suspend;

class ServerSocket
{
    protected SocketContext $context;
    private ?ServerEndPoint $endPoint = null;
    protected mixed $handle = null;
    private ?File $unixFile = null;

    public function __construct(?SocketContext $context = null)
    {
        $this->context = $context ?? new SocketContext();
    }

    public function isListening(): bool
    {
        return null !== $this->handle;
    }

    public function getLocalEndPoint(): ?ServerEndPoint
    {
        return $this->endPoint;
    }

    public function close(): void
    {
        if (null !== $this->handle) {
            @fclose($this->handle);
            $this->handle = null;
        }

        if ($this->unixFile && $this->unixFile->exists()) {
            $this->unixFile->delete();
        }
    }

    public function listen(ServerEndPoint $endPoint): void
    {
        $this->endPoint = $endPoint;

        if (!$this->handle) {
            if ($this->endPoint instanceof IPEndPoint) {
                $address = sprintf('tcp://%s:%s', $this->endPoint->getAddress(), $this->endPoint->getPort());
            } else {
                $address = $this->endPoint->getPath();
            }

            $this->handle = stream_socket_server($address, $errorNo, $errorMsg);
            stream_set_blocking($this->handle, false);

            foreach ($this->context->getOptions(SocketOptions::class) as $option => $value) {
                stream_context_set_option($this->handle, 'socket', $option, $value);
            }

            foreach ($this->context->getOptions(ServerSocketOptions::class) as $option => $value) {
                stream_context_set_option($this->handle, 'socket', $option, $value);
            }

            foreach ($this->context->getOptions(SecureOptions::class) as $name => $value) {
                stream_context_set_option($this->handle, 'ssl', $name, $value);
            }

            if ($this->endPoint instanceof UnixEndPoint) {
                $this->unixFile = new File($this->endPoint->getPath());
                $this->unixFile = $this->unixFile->getAbsoluteFile();
            }
        }
    }

    /**
     * Accept a new connection if a pending connection exists.
     *
     * @return Socket
     */
    public function accept(): Socket
    {
        if (!$this->isListening()) {
            throw new InvalidOperationException('Socket server is not configured for listening');
        }

        for (;;) {
            $sR = [$this->handle];
            $sW = null;
            $sE = null;

            if (0 !== stream_select($sR, $sW, $sE, 0)) {
                break;
            }

            Suspend();
        }

        // TODO: handle this if failed
        $handle = stream_socket_accept($this->handle);
        $socket = new Socket();

        Reflection::getReflectionMethod(Socket::class, 'initialize')->invoke($socket, $handle, true);

        return $socket;
    }
}
