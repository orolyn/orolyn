<?php
namespace Orolyn\Net\Sockets;

use Orolyn\Net\DnsEndPoint;
use Orolyn\Net\EndPoint;
use Orolyn\Net\Sockets\Options\SecureOptions;
use function Orolyn\Lang\Suspend;

class SecureSocket extends Socket
{
    /**
     * @inheritdoc
     */
    public function connect(EndPoint $endPoint, ?int $timeout = null): void
    {
        parent::connect($endPoint, $timeout);

        $options = $this->context->getOptions(SecureOptions::class);

        foreach ($options as $name => $value) {
            stream_context_set_option($this->handle, 'ssl', $name, $value);
        }

        if ($this->endPoint instanceof DnsEndPoint && null === $options->getPeerName()) {
            stream_context_set_option($this->handle, 'ssl', 'peer_name', $this->endPoint->getHost());
        }

        while (0 === $result = @stream_socket_enable_crypto($this->handle, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            Suspend();
        }

        if (false === $result) {
            $error = error_get_last()['message'];

            if (false !== $pos = strpos($error, '): ')) {
                $error = substr($error, $pos + 3);
            }

            throw new SocketException($error);
        }
    }
}
