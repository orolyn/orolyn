<?php
namespace Orolyn\IO;

use Orolyn\Endian;

trait EndianTrait
{
    private Endian $endian;

    public function getEndian(): Endian
    {
        return $this->endian ?? $this->endian = Endian::getDefault();
    }

    public function setEndian(Endian $endian): void
    {
        $this->endian = $endian;
    }
}
