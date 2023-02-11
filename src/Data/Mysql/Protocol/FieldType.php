<?php

namespace Orolyn\Data\Mysql\Protocol;

enum FieldType: int
{
    case MYSQL_TYPE_DECIMAL = 0;
    case MYSQL_TYPE_TINY = 1;
    case MYSQL_TYPE_SHORT = 2;
    case MYSQL_TYPE_LONG = 3;
    case MYSQL_TYPE_FLOAT = 4;
    case MYSQL_TYPE_DOUBLE = 5;
    case MYSQL_TYPE_NULL = 6;
    case MYSQL_TYPE_TIMESTAMP = 7;
    case MYSQL_TYPE_LONGLONG = 8;
    case MYSQL_TYPE_INT24 = 9;
    case MYSQL_TYPE_DATE = 10;
    case MYSQL_TYPE_TIME = 11;
    case MYSQL_TYPE_DATETIME = 12;
    case MYSQL_TYPE_YEAR = 13;
    case MYSQL_TYPE_NEWDATE = 14;
    case MYSQL_TYPE_VARCHAR = 15;
    case MYSQL_TYPE_BIT = 16;
    case MYSQL_TYPE_TIMESTAMP2 = 17;
    case MYSQL_TYPE_DATETIME2 = 18;
    case MYSQL_TYPE_TIME2 = 19;
    case MYSQL_TYPE_JSON = 245;
    case MYSQL_TYPE_NEWDECIMAL = 246;
    case MYSQL_TYPE_ENUM = 247;
    case MYSQL_TYPE_SET = 248;
    case MYSQL_TYPE_TINY_BLOB = 249;
    case MYSQL_TYPE_MEDIUM_BLOB = 250;
    case MYSQL_TYPE_LONG_BLOB = 251;
    case MYSQL_TYPE_BLOB = 252;
    case MYSQL_TYPE_VAR_STRING = 253;
    case MYSQL_TYPE_STRING = 254;
    case MYSQL_TYPE_GEOMETRY = 255;
}
