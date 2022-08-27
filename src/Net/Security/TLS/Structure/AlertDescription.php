<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\Net\Security\TLS\Context;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;

/**
 * enum {
 *     close_notify(0),
 *     unexpected_message(10),
 *     bad_record_mac(20),
 *     record_overflow(22),
 *     handshake_failure(40),
 *     bad_certificate(42),
 *     unsupported_certificate(43),
 *     certificate_revoked(44),
 *     certificate_expired(45),
 *     certificate_unknown(46),
 *     illegal_parameter(47),
 *     unknown_ca(48),
 *     access_denied(49),
 *     decode_error(50),
 *     decrypt_error(51),
 *     protocol_version(70),
 *     insufficient_security(71),
 *     internal_error(80),
 *     inappropriate_fallback(86),
 *     user_canceled(90),
 *     missing_extension(109),
 *     unsupported_extension(110),
 *     unrecognized_name(112),
 *     bad_certificate_status_response(113),
 *     unknown_psk_identity(115),
 *     certificate_required(116),
 *     no_application_protocol(120),
 *     (255)
 * } AlertDescription;
 */
enum AlertDescription: int implements IStructure
{
    case CloseNotify                    = 0;
    case UnexpectedMessage              = 10;
    case BadRecordMac                   = 20;
    case RecordOverflow                 = 22;
    case HandshakeFailure               = 40;
    case BadCertificate                 = 42;
    case UnsupportedCertificate         = 43;
    case CertificateRevoked             = 44;
    case CertificateExpired             = 45;
    case CertificateUnknown             = 46;
    case IllegalParameter               = 47;
    case UnknownCa                      = 48;
    case AccessDenied                   = 49;
    case DecodeError                    = 50;
    case DecryptError                   = 51;
    case ProtocolVersion                = 70;
    case InsufficientSecurity           = 71;
    case InternalError                  = 80;
    case InappropriateFallback          = 86;
    case UserCanceled                   = 90;
    case MissingExtension               = 109;
    case UnsupportedExtension           = 110;
    case UnrecognizedName               = 112;
    case BadCertificateStatusResponse   = 113;
    case UnknownPskIdentity             = 115;
    case CertificateRequired            = 116;
    case NoApplicationProtocol          = 120;

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
    public static function decode(IInputStream $stream, ?Context $context = null): static
    {
        return AlertDescription::from($stream->readUnsignedInt8());
    }
}
