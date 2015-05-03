<?php
namespace Orolyn;

use Orolyn\Collection\ArrayList;
use function Orolyn\Lang\Int16;
use function Orolyn\Lang\Int32;
use Orolyn\Primitive\TypeInt32;
use Orolyn\Serialization\ISerializable;
use Orolyn\Serialization\SerializationInfo;
use function Orolyn\Lang\Int8;

final class Guid implements ISerializable
{
    private $a = 0;
    private $b = 0;
    private $c = 0;
    private $d = 0;
    private $e = 0;
    private $f = 0;
    private $g = 0;
    private $h = 0;
    private $i = 0;
    private $j = 0;
    private $k = 0;

    /**
     * new Guid(char[16])
     * new Guid(char[32...])
     * new Guid(int32, int16, int16, char[8])
     * new Guid(int32, int16, int16, int8, int8, int8, int8, int8, int8, int8, int8)
     */
    public function __construct(...$args)
    {
        list($a, $b, $c, $d, $e, $f, $g, $h, $i, $j, $k) = $args;

        if (null === $a) {
            return;
        }

        if (TypeOf($a) === 'string') {
            if (String($a)->getLength() === 16) {
                $this->doParseBytes($a);
            } else {
                $this->doParseFormatted($a);
            }

            return;
        }

        if (TypeOf($a) === 'int' && TypeOf($d) === 'string') {
            if (String($d)->getLength() === 8) {
                $this->doCreateGuid($a, $b, $c, $d[0], $d[1], $d[2], $d[3], $d[4], $d[5], $d[6], $d[7]);
            } else {
                $this->doCreateGuid($a, $b, $c, $d, $e, $f, $g, $h, $i, $j, $k);
            }
        }
    }

    public static function createEmpty(): Guid
    {
        return new Guid();
    }

    public static function create(): Guid
    {

    }

    public static function parse(string $digits): Guid
    {
        $digits = String($digits);

        if ($digits->getLength() < 32) {
            throw new FormatException();
        }

        return new Guid($digits);
    }

    public static function parseExact(): Guid
    {

    }

    public function toString(): string
    {
    }

    public function getBytes(): string
    {
        return
            Int32($this->a)->getBytes().
            Int16($this->b)->getBytes().
            Int16($this->c)->getBytes().
            Int8($this->d)->getBytes().
            Int8($this->e)->getBytes().
            Int8($this->f)->getBytes().
            Int8($this->g)->getBytes().
            Int8($this->h)->getBytes().
            Int8($this->i)->getBytes().
            Int8($this->j)->getBytes().
            Int8($this->k)->getBytes();
    }

    private function doParseFormatted()
    {

    }

    private function doParseFormattedExact()
    {

    }

    private function doParseBytes(string $bytes)
    {

    }

    private function doCreateGuid(
        int $a,
        int $b,
        int $c,
        int $d,
        int $e,
        int $f,
        int $g,
        int $h,
        int $i,
        int $j,
        int $k
    ) {
        $this->a = Int32($a)->getValue();
        $this->a = Int16($b)->getValue();
        $this->a = Int16($c)->getValue();
        $this->a = Int8($d)->getValue();
        $this->a = Int8($e)->getValue();
        $this->a = Int8($f)->getValue();
        $this->a = Int8($g)->getValue();
        $this->a = Int8($h)->getValue();
        $this->a = Int8($i)->getValue();
        $this->a = Int8($j)->getValue();
        $this->a = Int8($k)->getValue();
    }
}
