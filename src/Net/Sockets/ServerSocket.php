<?php
namespace Orolyn\Net\Sockets;

use Orolyn\Exception;
use Orolyn\IO\File;
use Orolyn\Lang\InternalCaller;
use Orolyn\Net\IPEndPoint;
use Orolyn\Net\ServerEndPoint;
use Orolyn\Net\Sockets\Options\ServerSocketOptions;
use Orolyn\Net\Sockets\Options\SocketOptions;
use Orolyn\Net\UnixEndPoint;
use function Orolyn\Lang\Suspend;

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

            if ($this->endPoint instanceof UnixEndPoint) {
                $this->unixFile = new File($this->endPoint->getPath());
                $this->unixFile = $this->unixFile->getAbsoluteFile();
            }
        }
    }

    /**
     * Accept a new connection if a pending connection exists.
     *
     * @return Socket|null
     */
    public function accept(): ?Socket
    {
        if (!$this->isListening()) {
            return null;
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

        if ($handle = stream_socket_accept($this->handle)) {
            $socket = new Socket();
            InternalCaller::callMethod($socket, 'initialize', $handle);

            return $socket;
        }

        return null;
    }
}
