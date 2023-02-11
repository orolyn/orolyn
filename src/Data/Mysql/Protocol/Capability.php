<?php

namespace Orolyn\Data\Mysql\Protocol;

/**
 * @internal
 */
class Capability
{
    public const CLIENT_MYSQL                          = 0x1;
    public const CLIENT_FOUND_ROWS                     = 0x1 << 1;
    public const CLIENT_LONG_FLAG                      = 0x1 << 2;
    public const CLIENT_CONNECT_WITH_DB                = 0x1 << 3;
    public const CLIENT_NO_SCHEMA                      = 0x1 << 4;
    public const CLIENT_COMPRESS                       = 0x1 << 5;
    public const CLIENT_ODBC                           = 0x1 << 6;
    public const CLIENT_LOCAL_FILES                    = 0x1 << 7;
    public const CLIENT_IGNORE_SPACE                   = 0x1 << 8;
    public const CLIENT_PROTOCOL_41                    = 0x1 << 9;
    public const CLIENT_INTERACTIVE                    = 0x1 << 10;
    public const CLIENT_SSL                            = 0x1 << 11;
    public const CLIENT_IGNORE_SIGPIPE                 = 0x1 << 12;
    public const CLIENT_TRANSACTIONS                   = 0x1 << 13;
    public const CLIENT_RESERVED1                      = 0x1 << 14;
    public const CLIENT_SECURE_CONNECTION              = 0x1 << 15;

    // Extended
    public const CLIENT_MULTI_STATEMENTS               = 0x1 << 16;
    public const CLIENT_MULTI_RESULTS                  = 0x1 << 17;
    public const CLIENT_PS_MULTI_RESULTS               = 0x1 << 18;
    public const CLIENT_PLUGIN_AUTH                    = 0x1 << 19;
    public const CLIENT_CONNECT_ATTRS                  = 0x1 << 20;
    public const CLIENT_PLUGIN_AUTH_LENENC_CLIENT_DATA = 0x1 << 21;
    public const CLIENT_CAN_HANDLE_EXPIRED_PASSWORDS   = 0x1 << 22;
    public const CLIENT_SESSION_TRACK                  = 0x1 << 23;
    public const CLIENT_DEPRECATE_EOF                  = 0x1 << 24;

    public const CLIENT_ZSTD_COMPRESSION_ALGORITHM     = 0x1 << 26;
    public const CLIENT_CAPABILITY_EXTENSION           = 0x1 << 29;
    public const CLIENT_SSL_VERIFY_SERVER_CERT         = 0x1 << 30;
    public const CLIENT_REMEMBER_OPTIONS               = 0x1 << 31;

    // MariaDb
    public const MARIADB_CLIENT_PROGRESS               = 0x1 << 32;
    public const MARIADB_CLIENT_COM_MULTI              = 0x1 << 33;
    public const MARIADB_CLIENT_STMT_BULK_OPERATIONS   = 0x1 << 34;
    public const MARIADB_CLIENT_EXTENDED_TYPE_INFO	   = 0x1 << 35;
    public const MARIADB_CLIENT_CACHE_METADATA	       = 0x1 << 36;
}
