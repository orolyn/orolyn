<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;

/**
 * enum {
 *     server_name(0),                             RFC 6066
 *     max_fragment_length(1),                     RFC 6066
 *     status_request(5),                          RFC 6066
 *     supported_groups(10),                       RFC 8422, 7919
 *     signature_algorithms(13),                   RFC 8446
 *     use_srtp(14),                               RFC 5764
 *     heartbeat(15),                              RFC 6520
 *     application_layer_protocol_negotiation(16), RFC 7301
 *     signed_certificate_timestamp(18),           RFC 6962
 *     client_certificate_type(19),                RFC 7250
 *     server_certificate_type(20),                RFC 7250
 *     padding(21),                                RFC 7685
 *     RESERVED(40),                               Used but never assigned
 *     pre_shared_key(41),                         RFC 8446
 *     early_data(42),                             RFC 8446
 *     supported_versions(43),                     RFC 8446
 *     cookie(44),                                 RFC 8446
 *     psk_key_exchange_modes(45),                 RFC 8446
 *     RESERVED(46),                               Used but never assigned
 *     certificate_authorities(47),                RFC 8446
 *     oid_filters(48),                            RFC 8446
 *     post_handshake_auth(49),                    RFC 8446
 *     signature_algorithms_cert(50),              RFC 8446
 *     key_share(51),                              RFC 8446
 *     (65535)
 * } ExtensionType;
 */
enum ExtensionType: int implements IStructure
{
    case ServerName                             = 0;  /* RFC 6066 */
    case MaxFragmentLength                      = 1;  /* RFC 6066 */
    case StatusRequest                          = 5;  /* RFC 6066 */
    case SupportedGroups                        = 10; /* RFC 8422, 7919 */
    case SignatureAlgorithms                    = 13; /* RFC 8446 */
    case UseSrtp                                = 14; /* RFC 5764 */
    case Heartbeat                              = 15; /* RFC 6520 */
    case ApplicationLayerProtocolNegotiation    = 16; /* RFC 7301 */
    case SignedCertificateTimestamp             = 18; /* RFC 6962 */
    case ClientCertificateType                  = 19; /* RFC 7250 */
    case CertificateType                  = 20; /* RFC 7250 */
    case Padding                                = 21; /* RFC 7685 */
    case PreSharedKey                           = 41; /* RFC 8446 */
    case EarlyData                              = 42; /* RFC 8446 */
    case SupportedVersions                      = 43; /* RFC 8446 */
    case Cookie                                 = 44; /* RFC 8446 */
    case PskKeyExchangeModes                    = 45; /* RFC 8446 */
    case CertificateAuthorities                 = 47; /* RFC 8446 */
    case OidFilters                             = 48; /* RFC 8446 */
    case PostHandshakeAuth                      = 49; /* RFC 8446 */
    case SignatureAlgorithmsCert                = 50; /* RFC 8446 */
    case KeyShare                               = 51; /* RFC 8446 */

    /**
     * @inheritdoc
     */
    public function encode(IOutputStream $stream): void
    {
        $stream->writeUnsignedInt16($this->value);
    }

    /**
     * @inheritdoc
     */
    public static function decode(IInputStream $stream, ?bool $server = null): static
    {
        return ExtensionType::from($stream->readUnsignedInt16());
    }
}
