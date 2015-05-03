<?php
namespace Orolyn\IO;

use Orolyn\ArgumentException;
use Orolyn\InvalidOperationException;

enum FileMode
{
    case Append;
    case CreateNew;
    case Create;
    case Open;
    case Truncate;
}
