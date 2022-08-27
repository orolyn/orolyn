<?php

namespace Orolyn\Net\Security\TLS;

use Orolyn\Concurrency\Coroutine;
use Orolyn\Endian;
use Orolyn\IO\ByteQueueStream;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;
use Orolyn\Math;
use Orolyn\Net\Security\TLS\Crypto\Encryption;
use Orolyn\Net\Security\TLS\Structure\Alert;
use Orolyn\Net\Security\TLS\Structure\AlertLevel;
use Orolyn\Net\Security\TLS\Structure\ChangeCipherSpec;
use Orolyn\Net\Security\TLS\Structure\ContentType;
use Orolyn\Net\Security\TLS\Structure\Handshake;
use Orolyn\Net\Security\TLS\Structure\HandshakeType;
use Orolyn\Net\Security\TLS\Structure\IStructure;
use Orolyn\Net\Security\TLS\Structure\Record;
use Orolyn\Net\Security\TLS\Structure\Structure;
use RuntimeException;

class RecordLayer
{
    public ?Encryption $encryption = null;
    private ?ContentType $currentContentType = null;
    private ?IStructure $currentData = null;

    public function __construct(
        private IInputStream $input,
        private IOutputStream $output,
        private bool $server
    ) {
    }

    /**
     * @return bool
     */
    public function isEncryptionEnabled(): bool
    {
        return null !== $this->encryption;
    }

    public function send(ContentType $contentType, IStructure $data): void
    {
        $byteStream = Structure::createByteStream($data);

        while (($length = Math::min($byteStream->getBytesAvailable(), 2**14)) > 0) {
            $record = new Record(
                $contentType,
                $byteStream->read($length)
            );

            $record->encode($this->output);
        }

        $this->output->flush();
    }

    /**
     * @param ContentType $contentType
     * @return IStructure
     * @throws AlertException
     */
    public function require(ContentType $contentType, ?HandshakeType $handshakeType = null): IStructure
    {
        if (null === $data = $this->receive($contentType, $handshakeType)) {
            throw new RuntimeException();
        }

        return $data;
    }

    /**
     * @param ContentType $contentType
     * @param HandshakeType|null $handshakeType
     * @return IStructure|null
     * @throws AlertException
     */
    public function receive(ContentType $contentType, ?HandshakeType $handshakeType = null): ?IStructure
    {
        if (null === $data = $this->currentData) {
            $record = Record::decode($this->input, $this->server);

            if (null !== $this->encryption) {
                if (ContentType::ApplicationData === $record->contentType) {
                    $record = $this->encryption->decrypt($record);
                } elseif (ContentType::ChangeCipherSpec !== $record->contentType) {
                    throw new RuntimeException();
                }
            }

            $stream = new RecordByteQueueStream();
            $stream->setEndian(Endian::BigEndian);
            $stream->write($record->bytes);

            $this->currentContentType = $record->contentType;
            $data = $this->currentData = match ($record->contentType) {
                ContentType::Alert => $this->doReceiveAlert($stream),
                ContentType::ChangeCipherSpec => $this->doReceiveChangeCipherSpec($stream),
                ContentType::Handshake => $this->doReceiveHandshake($stream),
                ContentType::ApplicationData => throw new RuntimeException()
            };
        }

        if (ContentType::Alert === $this->currentContentType) {
            throw $data->createException();
        }

        if ($this->currentContentType !== $contentType) {
            return null;
        }

        if (
            ContentType::Handshake === $this->currentContentType &&
            (null !== $handshakeType) &&
            $data->handshakeType !== $handshakeType
        ) {
            return null;
        }

        $this->currentContentType = null;
        $this->currentData = null;

        return $data;
    }

    private function doReceiveAlert(ByteQueueStream $stream): Alert
    {
        $data = Alert::decode($stream, $this->server);

        if ($stream->getBytesAvailable() > 0) {
            throw new RuntimeException('Expected end of record');
        }

        return $data;
    }

    private function doReceiveChangeCipherSpec(ByteQueueStream $stream): ChangeCipherSpec
    {
        $data = ChangeCipherSpec::decode($stream, $this->server);

        if ($stream->getBytesAvailable() > 0) {
            throw new RuntimeException('Expected end of record');
        }

        return $data;
    }

    private function doReceiveHandshake(ByteQueueStream $stream): Handshake
    {
        $coroutine = new Coroutine(fn () => Handshake::decode($stream, $this->server));
        $coroutine->start();

        while (!$coroutine->isCompleted()) {

            $record = Record::decode($this->input, $this->server);

            if (ContentType::Handshake !== $record->contentType) {
                throw new RuntimeException('Expected handshake record');
            }

            $stream->write($record->bytes);
            $coroutine->resume();
        }

        $result = $coroutine->getResult();

        if ($stream->getBytesAvailable() > 0) {
            // multi-message record
        }

        return $result;
    }
}
