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

trait OutputStreamTrait
{
    abstract public function getEndian(): Endian;

    abstract public function write(string $bytes): void;

    public function writeInt8($value): void
    {
        $this->write(Int8($value)->getBytes());
    }

    public function writeInt16($value): void
    {
        $this->write($this->getEndian()->convert(Int16($value)->getBytes()));
    }

    public function writeInt32($value): void
    {
        $this->write($this->getEndian()->convert(Int32($value)->getBytes()));
    }

    public function writeInt64($value): void
    {
        $this->write($this->getEndian()->convert(Int64($value)->getBytes()));
    }

    public function writeUnsignedInt8($value): void
    {
        $this->write(UnsignedInt8($value)->getBytes());
    }

    public function writeUnsignedInt16($value): void
    {
        $this->write($this->getEndian()->convert(UnsignedInt16($value)->getBytes()));
    }

    public function writeUnsignedInt32($value): void
    {
        $this->write($this->getEndian()->convert(UnsignedInt32($value)->getBytes()));
    }

    public function writeUnsignedInt64($value): void
    {
        $this->write($this->getEndian()->convert(UnsignedInt64($value)->getBytes()));
    }

    public function writeFloat($value): void
    {
        $this->write($this->getEndian()->convert(Float($value)->getBytes()));
    }

    public function writeDouble($value): void
    {
        $this->write($this->getEndian()->convert(Double($value)->getBytes()));
    }

    public function writeBool($value): void
    {
        $this->write(Bool($value)->getBytes());
    }

    public function writeObject(object $value): void
    {

    }
}
