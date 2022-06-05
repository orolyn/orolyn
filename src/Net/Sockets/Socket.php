<?php
namespace Orolyn\Net\Sockets;

use Orolyn\ArgumentOutOfRangeException;
use Orolyn\IO\Buffer;
use Orolyn\IO\EndianTrait;
use Orolyn\IO\IInputStream;
use Orolyn\IO\InputStreamTrait;
use Orolyn\IO\IOutputStream;
use Orolyn\IO\OutputStreamTrait;
use Orolyn\Net\DnsEndPoint;
use Orolyn\Net\DnsResolver;
use Orolyn\Net\EndPoint;
use Orolyn\Net\IPEndPoint;
use Orolyn\Net\Sockets\Options\SocketOptions;
use Orolyn\Net\UnixEndPoint;
use Orolyn\NotImplementedException;
use Orolyn\Timer;
use function Orolyn\Lang\Suspend;

class Socket implements IInputStream, IOutputStream
{
    private const I_BUFFER_SIZE = 1024 ** 3;
    private const O_BUFFER_SIZE = 1024 ** 3;

    private const SELECT_R = 1;
    private const SELECT_W = 1 << 1;

    use EndianTrait;
    use InputStreamTrait;
    use OutputStreamTrait;

    protected SocketContext $context;
    protected ?EndPoint $endPoint = null;
    protected bool $connected = false;
    protected mixed $handle = null;
    private Buffer $iBuf;
    private Buffer $oBuf;

    /**
     * @param SocketContext|null $context
     */
    public function __construct(?SocketContext $context = null)
    {
        $this->context = $context ?? new SocketContext();
        $this->iBuf = new Buffer();
        $this->oBuf = new Buffer();
    }

    /**
     * @param EndPoint $endPoint
     * @param int|null $timeout
     * @throws SocketConnectionTimeoutException
     * @throws SocketException
     */
    public function connect(EndPoint $endPoint, ?int $timeout = null): void
    {
        $this->endPoint = $endPoint;
        $timeout = $timeout ?? (ini_get('default_socket_timeout') * 1000);

        if ($endPoint instanceof DnsEndPoint) {
            $entry = DnsResolver::lookup($endPoint->getHost());

            foreach ($entry->getAddressList() as $ipAddress) {
                if ($this->tryConnectToEndPoint(new IPEndPoint($ipAddress, $endPoint->getPort()), $timeout, false)) {
                    break;
                }
            }

            if (!$this->connected) {
                throw new SocketException('Unable to connect to resolved addresses');
            }
        } elseif ($endPoint instanceof IPEndPoint || $endPoint instanceof UnixEndPoint) {
            $this->tryConnectToEndPoint($endPoint, $timeout, true);
        } else {
            throw new NotImplementedException('Unknown endpoint type');
        }
    }

    /**
     * @param IPEndPoint|UnixEndPoint $endPoint
     * @param int $timeout
     * @param bool $throwException
     * @return bool
     * @throws SocketConnectionTimeoutException
     * @throws SocketException
     */
    private function tryConnectToEndPoint(IPEndPoint|UnixEndPoint $endPoint, int $timeout, bool $throwException): bool
    {
        if ($endPoint instanceof IPEndPoint) {
            $address = sprintf('tcp://%s:%s', $endPoint->getAddress(), $endPoint->getPort());
        } else {
            $address = $endPoint->getPath();
        }

        $handle = stream_socket_client(
            $address,
            $errorNo,
            $errorMsg,
            0,
            STREAM_CLIENT_ASYNC_CONNECT
        );

        foreach ($this->context->getOptions(SocketOptions::class) as $option => $value) {
            stream_context_set_option($handle, 'socket', $option, $value);
        }

        $timer = $timeout > 0 ? new Timer($timeout) : null;

        while ((0 === $select = $this->select($handle, self::SELECT_W)) && (!$timer || !$timer->isExpired())) {
            Suspend();
        }

        if (0 === $select) {
            fclose($handle);

            if ($throwException) {
                throw new SocketConnectionTimeoutException();
            }

            return false;
        }

        $this->initialize($handle);

        return true;
    }

    /**
     * Internally called by ServerSocket
     *
     * @param $handle
     * @return void
     * @throws SocketException
     */
    protected function initialize($handle): void
    {
        $this->handle = $handle;
        $this->connected = true;

        if (!stream_set_blocking($this->handle, false)) {
            $this->throwLastError();
        }
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->connected;
    }

    /**
     * @inheritdoc
     */
    public function isEndOfStream(): bool
    {
        return !($this->isConnected() || ($this->getBytesAvailable() > 0));
    }

    /**
     * {@inheritdoc}
     *
     * @throws SocketException
     * @throws SocketNotConnectedException
     */
    public function peek(int $length = 1): string
    {
        if ($length < 0) {
            throw new ArgumentOutOfRangeException('length');
        }

        if ($this->iBuf->getLength() >= $length) {
            return $this->iBuf->readAhead($length);
        }

        if (null !== $bytes = $this->read($length)) {
            $this->iBuf->unshift($bytes);
        }

        return $bytes;
    }

    /**
     * {@inheritdoc}
     *
     * @throws SocketException
     * @throws SocketNotConnectedException
     */
    public function read(int $length = 1): string
    {
        if ($length < 0) {
            throw new ArgumentOutOfRangeException('length');
        }

        $available = $this->getBytesAvailable();

        // Enough bytes are present in the buffer for this read so just take from there.
        if ($available >= $length) {
            return $this->iBuf->dequeue($length);
        }

        if (!$this->isConnected()) {
            throw new SocketNotConnectedException();
        }

        $bytes = $this->iBuf->restart();

        for (;;) {
            if (false === $available = $this->getInput()) {
                $this->throwLastError();
            }

            if (0 === $available) {
                if (!$this->isConnected()) {
                    throw new SocketNotConnectedException();
                }

                Suspend();
                continue;
            }

            $remainingLength = $length - strlen($bytes);

            if ($this->iBuf->getLength() >= $remainingLength) {
                $bytes .= $this->iBuf->dequeue($remainingLength);

                break;
            }

            $bytes .= $this->iBuf->restart();

            if (!$this->isConnected() && strlen($bytes) < $length) {
                throw new SocketNotConnectedException();
            }
        }

        return $bytes;
    }

    /**
     * @inheritdoc
     */
    public function getLength(): int
    {
        return 0;
    }

    /**
     * @inheritdoc
     */
    public function getBytesAvailable(): int
    {
        $this->getInput();

        return $this->iBuf->getLength();
    }

    /**
     * @return int|bool
     */
    private function getInput(): int|bool
    {
        if (!$this->connected) {
            return $this->iBuf->getLength();
        }

        while ($this->iBuf->getLength() < self::I_BUFFER_SIZE) {
            if (false === $bytes = @stream_get_contents($this->handle, 65536)) {
                return false;
            }

            if ('' === $bytes) {
                if (feof($this->handle)) {
                    $this->close();
                }

                break;
            }

            $this->iBuf->enqueue($bytes);
        }

        return $this->iBuf->getLength();
    }

    /**
     * {@inheritdoc}
     *
     * @throws SocketNotConnectedException
     */
    public function write(string $bytes): void
    {
        if (!$this->isConnected()) {
            throw new SocketNotConnectedException();
        }

        $this->oBuf->enqueue($bytes);
    }

    /**
     * @throws SocketException
     * @throws SocketNotConnectedException
     */
    public function flush(): void
    {
        if (!$this->connected) {
            throw new SocketNotConnectedException();
        }

        while (($outputLength = $this->oBuf->getLength()) > 0) {
            $sR = null;
            $sW = [$this->handle];
            $sE = null;

            $select = stream_select($sR, $sW, $sE, 0);

            if (false === $select) {
                $this->throwLastError();
            }

            if (empty($sW)) {
                Suspend();
                continue;
            }

            if (false === $written = @fwrite($this->handle, $this->oBuf->readAhead($outputLength), $outputLength)) {
                $this->throwLastError();
            }

            if ($written > 0) {
                $this->oBuf->skip($written);
            }
        }
    }

    /**
     * @param int $position
     * @throws SocketException
     * @throws SocketNotConnectedException
     */
    public function setPosition(int $position): void
    {
        if (0 === $position) {
            return;
        }

        if ($position < 0) {
            throw new ArgumentOutOfRangeException('length');
        }

        $this->read($position);
    }

    /**
     * @inheritdoc
     */
    public function getPosition(): int
    {
        return 0;
    }

    /**
     * @return int
     */
    public function getBytesPending(): int
    {
        return $this->oBuf->getLength();
    }

    /**
     * Close the socket.
     */
    public function close(): void
    {
        $this->connected = false;

        if (!$this->handle) {
            return;
        }

        @fclose($this->handle);
        $this->handle = null;
    }

    /**
     * @throws SocketException
     */
    protected function throwLastError(): void
    {
        $message = error_get_last()['message'];
        $this->close();

        throw new SocketException($message);
    }

    private function select($handle, int $flags): int|bool
    {
        $sR = $flags & self::SELECT_R ? [$handle] : null;
        $sW = $flags & self::SELECT_W ? [$handle] : null;
        $sE = null;

        if (false === $select = stream_select($sR, $sW, $sE, 0)) {
            return false;
        }

        if (0 === $select) {
            return 0;
        }

        return (!empty($sR) ? self::SELECT_R : 0) | (!empty($sW) ? self::SELECT_W : 0);
    }
}
