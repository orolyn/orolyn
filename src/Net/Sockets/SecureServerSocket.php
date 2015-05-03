<?php
namespace Orolyn\Net\Sockets;

use Orolyn\IO\File;
use Orolyn\Lang\InternalCaller;
use Orolyn\Net\IPEndPoint;
use Orolyn\Net\ServerEndPoint;
use Orolyn\Net\Sockets\Options\SecureOptions;
use Orolyn\Net\UnixEndPoint;
use function Orolyn\Lang\Suspend;

class SecureServerSocket extends ServerSocket
{
    public function listen(ServerEndPoint $endPoint): void
    {
        parent::listen($endPoint);

        if ($this->handle) {
            foreach ($this->context->getOptions(SecureOptions::class) as $name => $value) {
                stream_context_set_option($this->handle, 'ssl', $name, $value);
            }
        }
    }

    public function accept(): ?Socket
    {
        if (!$this->isListening()) {
            return null;
        }

        if ($handle = @stream_socket_accept($this->handle)) {
            $socket = new SecureSocket();
            InternalCaller::callMethod($socket, 'initialize', $handle);

            while (0 === $result = @stream_socket_enable_crypto($handle, true, STREAM_CRYPTO_METHOD_TLS_SERVER)) {
                Suspend();
            }

            if (false !== $result) {
                return $socket;
            }
        }

        return null;
    }
}
