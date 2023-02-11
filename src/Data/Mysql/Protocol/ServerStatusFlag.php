<?php

namespace Orolyn\Data\Mysql\Protocol;

class ServerStatusFlag: int
{
    public const SERVER_STATUS_IN_TRANS             = 1;
    public const SERVER_STATUS_AUTOCOMMIT           = 1 << 1;
    public const SERVER_MORE_RESULTS_EXISTS         = 1 << 2;
    public const SERVER_QUERY_NO_GOOD_INDEX_USED    = 1 << 3;
    public const SERVER_QUERY_NO_INDEX_USED         = 1 << 4;
    public const SERVER_STATUS_CURSOR_EXISTS        = 1 << 5;
    public const SERVER_STATUS_LAST_ROW_SENT        = 1 << 6;
    public const SERVER_STATUS_DB_DROPPED           = 1 << 7;
    public const SERVER_STATUS_NO_BACKSLASH_ESCAPES = 1 << 8;
    public const SERVER_STATUS_METADATA_CHANGED     = 1 << 9;
    public const SERVER_QUERY_WAS_SLOW              = 1 << 11;
    public const SERVER_PS_OUT_PARAMS               = 1 << 12;
    public const SERVER_STATUS_IN_TRANS_READONLY    = 1 << 13;
    public const SERVER_SESSION_STATE_CHANGED       = 1 << 14;
}
