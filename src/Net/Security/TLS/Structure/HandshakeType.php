<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;

/**
 * enum {
 *     client_hello(1),
 *     server_hello(2),
 *     new_session_ticket(4),
 *     end_of_early_data(5),
 *     encrypted_extensions(8),
 *     certificate(11),
 *     certificate_request(13),
 *     certificate_verify(15),
 *     finished(20),
 *     key_update(24),
 *     message_hash(254),
 *     (255)
 * } HandshakeType;
 */
enum HandshakeType: int implements IStructure
{
    case ClientHello            = 1;
    case ServerHello            = 2;
    case NewSessionTicket       = 4;
    case EndOfEarlyData         = 5;
    case EncryptedExtensions    = 8;
    case Certificate            = 11;
    case CertificateRequest     = 13;
    case CertificateVerify      = 15;
    case Finished               = 20;
    case KeyUpdate              = 24;
    case MessageHash            = 254;
    
    /**
     * @inheritdoc
     */
    public function encode(IOutputStream $stream): void
    {
        $stream->writeUnsignedInt8($this->value);
    }

    /**
     * @inheritdoc
     */
    public static function decode(IInputStream $stream, ?bool $server = null): static
    {
        return HandshakeType::from($stream->readUnsignedInt8());
    }
}
