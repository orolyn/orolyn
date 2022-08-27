<?php

namespace Orolyn\Data\Mysql;

/**
 * @internal
 */
class MysqlCapability
{
    public const CLIENT_LONG_PASSWORD                  = 0x1;
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
    public const CLIENT_RESERVED                       = 0x1 << 14;
    public const CLIENT_SECURE_CONNECTION              = 0x1 << 15;
    public const CLIENT_MULTI_STATEMENTS               = 0x1 << 16;
    public const CLIENT_MULTI_RESULTS                  = 0x1 << 17;
    public const CLIENT_PS_MULTI_RESULTS               = 0x1 << 18;
    public const CLIENT_PLUGIN_AUTH                    = 0x1 << 19;
    public const CLIENT_CONNECT_ATTRS                  = 0x1 << 20;
    public const CLIENT_PLUGIN_AUTH_LENENC_CLIENT_DATA = 0x1 << 21;
    public const CLIENT_CAN_HANDLE_EXPIRED_PASSWORDS   = 0x1 << 22;
    public const CLIENT_SESSION_TRACK                  = 0x1 << 23;
    public const CLIENT_DEPRECATE_EOF                  = 0x1 << 24;
}
