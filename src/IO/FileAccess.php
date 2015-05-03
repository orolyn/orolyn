<?php
namespace Orolyn\IO;

use Orolyn\ArgumentException;
use Orolyn\InvalidOperationException;

enum FileAccess
{
    case Read;
    case ReadWrite;
    case Write;

    /**
     * @return bool
     */
    public function canRead(): bool
    {
        return $this === self::Read || $this === self::ReadWrite;
    }

    /**
     * @return bool
     */
    public function canWrite(): bool
    {
        return $this === self::Write || $this === self::ReadWrite;
    }
}
