<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\Net\Security\TLS\Context;
use Exception;
use Orolyn\ArgumentException;
use Orolyn\Collection\Dictionary;
use Orolyn\IO\ByteStream;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;
use RuntimeException;

/**
 * struct {
 *     HandshakeType msg_type;   //handshake type
 *     uint24 length;             // remaining bytes in message
 *     select (Handshake.msg_type) {
 *         case client_hello:          ClientHello;
 *         case server_hello:          ServerHello;
 *         case end_of_early_data:     EndOfEarlyData;
 *         case encrypted_extensions:  EncryptedExtensions;
 *         case certificate_request:   CertificateRequest;
 *         case certificate:           Certificate;
 *         case certificate_verify:    CertificateVerify;
 *         case finished:              Finished;
 *         case new_session_ticket:    NewSessionTicket;
 *         case key_update:            KeyUpdate;
 *     };
 * } Handshake;
 */
class Handshake extends Structure
{
    public readonly ClientHello $clientHello;
    public readonly ServerHello $serverHello;
    public readonly ExtensionList $encryptedExtensions;
    public readonly CertificateRequest $certificateRequest;
    public readonly Certificate $certificate;
    public readonly CertificateVerify $certificateVerify;
    public readonly Finished $finished;

    public function __construct(
        public readonly HandshakeType $handshakeType,
        public readonly IStructure $data
    ) {
        $class = self::getStructureClass($this->handshakeType);

        if (!$data instanceof $class) {
            throw new ArgumentException(
                sprintf(
                    'Argument "data" must be instance of "%s" for handshake type "%s"',
                    $class,
                    $this->handshakeType->name
                )
            );
        }

        $property = match ($class) {
            ClientHello::class => 'clientHello',
            ServerHello::class => 'serverHello',
            EncryptedExtensionList::class => 'encryptedExtensions',
            CertificateRequest::class => 'certificateRequest',
            Certificate::class => 'certificate',
            CertificateVerify::class => 'certificateVerify',
            Finished::class => 'finished',
        };

        $this->{$property} = $data;
    }

    /**
     * @param HandshakeType $type
     * @return void
     */
    public function assertType(HandshakeType $type): void
    {
        if (HandshakeType::ServerHello !== $type) {
            throw new RuntimeException();
        }
    }

    /**
     * @inheritdoc
     */
    public function encode(IOutputStream $stream): void
    {
        if (null === $this->contentCache) {
            $cache = self::createByteStream();

            $this->handshakeType->encode($cache);
            $byteStream = self::createByteStream($this->data);
            $length = $byteStream->getLength();
            $cache->writeUnsignedInt24($length);
            $cache->write($byteStream);

            $this->contentCache = (string)$cache;
        }

        $stream->write($this->contentCache);

    }

    /**
     * @inheritdoc
     */
    public static function decode(IInputStream $stream, ?Context $context = null): static
    {
        $handshakeType = HandshakeType::from($stream->readUnsignedInt8());
        $byteStream = self::createByteStream($stream->read($stream->readUnsignedInt24()));

        /** @var class-string<IStructure> $class */
        $class = self::getStructureClass($handshakeType);
        $data = $class::decode($byteStream, $context);

        return new Handshake(
            $handshakeType,
            $data
        );
    }

    /**
     * @param HandshakeType $handshakeType
     * @return string
     */
    private static function getStructureClass(HandshakeType $handshakeType): string
    {
        static $map;

        if (null === $map) {
            $map = new Dictionary();
            $map->add(HandshakeType::ClientHello, ClientHello::class);
            $map->add(HandshakeType::ServerHello, ServerHello::class);
            $map->add(HandshakeType::EncryptedExtensions, EncryptedExtensionList::class);
            $map->add(HandshakeType::CertificateRequest, CertificateRequest::class);
            $map->add(HandshakeType::Certificate, Certificate::class);
            $map->add(HandshakeType::CertificateVerify, CertificateVerify::class);
            $map->add(HandshakeType::Finished, Finished::class);
        }

        return $map->get($handshakeType);
    }
}
