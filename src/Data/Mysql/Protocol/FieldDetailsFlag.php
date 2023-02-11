<?php

namespace Orolyn\Data\Mysql\Protocol;

final class FieldDetailsFlag
{
    public const NOT_NULL = 1;
    public const PRIMARY_KEY = 2;
    public const UNIQUE_KEY = 4;
    public const MULTIPLE_KEY = 8;
    public const BLOB = 16;
    public const UNSIGNED = 32;
    public const ZEROFILL_FLAG = 64;
    public const BINARY_COLLATION = 128;
    public const ENUM = 256;
    public const AUTO_INCREMENT = 512;
    public const TIMESTAMP = 1024;
    public const SET = 2048;
    public const NO_DEFAULT_VALUE_FLAG = 4096;
    public const ON_UPDATE_NOW_FLAG = 8192;
    public const NUM_FLAG = 32768;
}
