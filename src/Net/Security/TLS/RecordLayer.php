<?php

namespace Orolyn\Net\Security\TLS;

use Orolyn\Collection\ArrayList;
use Orolyn\Collection\IList;
use Orolyn\Collection\ImmutableList;
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
    private IList $currentHandshakes;
    public string $handshakeBytes = '';

    public function __construct(
        private IInputStream $input,
        private IOutputStream $output,
        private Context $context
    ) {
        $this->currentHandshakes = new ArrayList();
    }

    /**
     * @return IList<Handshake>
     */
    public function getCurrentHandshakes(): IList
    {
        return $this->currentHandshakes->copyImmutableList();
    }

    /**
     * @return bool
     */
    public function isEncryptionEnabled(): bool
    {
        return null !== $this->encryption;
    }

    public function sendHandshake(Handshake $handshake): void
    {
        $this->send(ContentType::Handshake, $handshake);
    }

    public function sendAlert(Alert $alert): void
    {
        $this->send(ContentType::Alert, $alert);
    }

    public function sendChangeCipherSpec(): void
    {
        $this->send(ContentType::ChangeCipherSpec, new ChangeCipherSpec());
    }

    public function sendApplicationData($applicationData): void
    {
        $this->send(ContentType::ApplicationData, $applicationData);
    }

    private function send(ContentType $contentType, IStructure $data): void
    {
        $byteStream = Structure::createByteStream($data);

        if (ContentType::Handshake === $contentType) {
            $this->currentHandshakes[] = $data;
            $this->handshakeBytes .= $byteStream;
        }

        while (($length = Math::min($byteStream->getBytesAvailable(), 2**14)) > 0) {
            $record = new Record(
                $contentType,
                $byteStream->read($length)
            );

            if ($this->encryption && ContentType::ChangeCipherSpec !== $contentType) {
                $record = $this->encryption->encrypt($record);
            }

            $record->encode($this->output);
        }

        $this->output->flush();
    }

    public function requireHandshake(?HandshakeType $handshakeType = null): Handshake
    {
        return $this->require(ContentType::Handshake, $handshakeType);
    }

    public function receiveHandshake(?HandshakeType $handshakeType = null): ?Handshake
    {
        return $this->receive(ContentType::Handshake, $handshakeType);
    }

    public function requireAlert(): Alert
    {
        return $this->require(ContentType::Alert);
    }

    public function receiveAlert(): ?Alert
    {
        return $this->receive(ContentType::Alert);
    }

    public function requireChangeCipherSpec(): ChangeCipherSpec
    {
        return $this->require(ContentType::ChangeCipherSpec);
    }

    public function receiveChangeCipherSpec(): ?ChangeCipherSpec
    {
        return $this->receive(ContentType::ChangeCipherSpec);
    }

    public function requireApplicationData()
    {
        return $this->require(ContentType::ApplicationData);
    }

    public function receiveApplicationData()
    {
        return $this->receive(ContentType::ApplicationData);
    }

    /**
     * @param ContentType $contentType
     * @return IStructure
     * @throws AlertException
     */
    private function require(ContentType $contentType, ?HandshakeType $handshakeType = null): IStructure
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
    private function receive(ContentType $contentType, ?HandshakeType $handshakeType = null): ?IStructure
    {
        if (null === $data = $this->currentData) {
            $record = Record::decode($this->input, $this->context);

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

        if (ContentType::Alert === $this->currentContentType && ContentType::Alert !== $contentType) {
            throw $data->createException();
        }

        if ($this->currentContentType !== $contentType) {
            return null;
        }

        if (ContentType::Handshake === $this->currentContentType) {
            if ((null !== $handshakeType) && $data->handshakeType !== $handshakeType) {
                return null;
            }

            $this->currentHandshakes[] = $data;
        }

        $this->currentContentType = null;
        $this->currentData = null;

        return $data;
    }

    private function doReceiveAlert(ByteQueueStream $stream): Alert
    {
        $data = Alert::decode($stream);

        if ($stream->getBytesAvailable() > 0) {
            throw new RuntimeException('Expected end of record');
        }

        return $data;
    }

    private function doReceiveChangeCipherSpec(ByteQueueStream $stream): ChangeCipherSpec
    {
        $data = ChangeCipherSpec::decode($stream);

        if ($stream->getBytesAvailable() > 0) {
            throw new RuntimeException('Expected end of record');
        }

        return $data;
    }

    private function doReceiveHandshake(ByteQueueStream $stream): Handshake
    {
        $this->handshakeBytes .= $stream;
        $coroutine = new Coroutine(fn () => Handshake::decode($stream, $this->context));
        $coroutine->start();

        while (!$coroutine->isCompleted()) {

            $record = Record::decode($this->input);

            if (ContentType::Handshake !== $record->contentType) {
                throw new RuntimeException('Expected handshake record');
            }

            $stream->write($record->bytes);
            $this->handshakeBytes .= $stream;
            $coroutine->resume();
        }

        $result = $coroutine->getResult();

        if ($stream->getBytesAvailable() > 0) {
            // multi-message record
        }

        return $result;
    }
}
