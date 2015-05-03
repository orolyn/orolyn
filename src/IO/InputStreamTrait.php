<?php
namespace Orolyn\IO;

use Orolyn\Endian;
use function Orolyn\Lang\Bool;
use function Orolyn\Lang\Double;
use function Orolyn\Lang\Float;
use function Orolyn\Lang\Int16;
use function Orolyn\Lang\Int32;
use function Orolyn\Lang\Int64;
use function Orolyn\Lang\Int8;
use function Orolyn\Lang\UnsignedInt16;
use function Orolyn\Lang\UnsignedInt32;
use function Orolyn\Lang\UnsignedInt64;
use function Orolyn\Lang\UnsignedInt8;

trait InputStreamTrait
{
    abstract public function getEndian(): Endian;

    abstract public function read(int $length = 1): ?string;

    public function readInt8(): int
    {
        return Int8($this->read(1))->getValue();
    }

    public function readInt16(): int
    {
        return Int16($this->getEndian()->convert($this->read(2)))->getValue();
    }

    public function readInt32(): int
    {
        return Int32($this->getEndian()->convert($this->read(4)))->getValue();
    }

    public function readInt64(): int
    {
        return Int64($this->getEndian()->convert($this->read(8)))->getValue();
    }

    public function readUnsignedInt8(): int
    {
        return UnsignedInt8($this->getEndian()->convert($this->read(1)))->getValue();
    }

    public function readUnsignedInt16(): int
    {
        return UnsignedInt16($this->getEndian()->convert($this->read(2)))->getValue();
    }

    public function readUnsignedInt32(): int
    {
        return UnsignedInt32($this->getEndian()->convert($this->read(4)))->getValue();
    }

    public function readUnsignedInt64(): int
    {
        return UnsignedInt64($this->getEndian()->convert($this->read(8)))->getValue();
    }

    public function readFloat(): float
    {
        return Float($this->getEndian()->convert($this->read(4)))->getValue();
    }

    public function readDouble(): float
    {
        return Double($this->getEndian()->convert($this->read(8)))->getValue();
    }

    public function readBool(): bool
    {
        return Bool($this->read(1))->getValue();
    }
}
