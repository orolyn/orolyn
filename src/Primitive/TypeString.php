<?php
namespace Orolyn\Primitive;

use Orolyn\ArgumentOutOfRangeException;
use Orolyn\Collection\ArrayList;
use Orolyn\Collection\ICollection;
use Orolyn\EqualityComparer;
use function Orolyn\Lang\Native;
use function Orolyn\Lang\String;

final class TypeString extends Primitive
{
    private $length;

    public function __construct($value)
    {
        if (is_object($value) && method_exists($value, '__toString')) {
            $value = $value->__toString();
        } elseif (!is_string($value) && is_scalar($value)) {
            $value = (string)$value;
        }

        parent::__construct($value);

        $this->length = strlen($value);
    }

    public function equals($value): bool
    {
        if ($value instanceof TypeString) {
            return $this->value === $value->value;
        }

        return EqualityComparer::getDefault()->equate($this->value, $value);
    }

    public function getBytes(): string
    {
        return $this->value;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public static function join(string $delimiter, ICollection $strings): TypeString
    {
        return String(implode($delimiter, Native($strings)));
    }

    public function format(string ...$params): TypeString
    {
        return String(sprintf($this->value, ...$params));
    }

    public function replace(string $search, string $replace): TypeString
    {
        return String(str_replace($search, $replace, $this->value));
    }

    public function substring(int $position, int $length = null): TypeString
    {
        if ($position < 0) {
            throw new ArgumentOutOfRangeException('position');
        }

        if ($length < 0) {
            throw new ArgumentOutOfRangeException('length');
        }

        if (0 === $length) {
            return String('');
        }

        if (null === $length) {
            if ($position >= $this->length) {
                return String('');
            }

            return String(substr($this->value, $position));
        }

        $string = (string)substr($this->value, $position, $length);

        if ($length > $this->length - $position) {
            $string = str_pad($string, $length, "\0", STR_PAD_RIGHT);
        }

        return String($string);
    }

    public function insert(int $position, string $string): TypeString
    {
        if ($position < 0) {
            throw new ArgumentOutOfRangeException('position');
        }

        $bytes = $this->value;
        $end = $position + strlen($string);

        if ($position > $this->length) {
            return String(str_pad($this->value, $position, "\0", STR_PAD_RIGHT) . $string);
        }

        for ($i = $position, $j = 0; $i < $end; $i++, $j++) {
            $bytes[$i] = $string[$j];
        }

        return String($bytes);
    }

    public function padLeft(int $length, string $char = ' '): TypeString
    {
        if (strlen($char) === 0) {
            return $this;
        }

        return String(str_pad($this->value, $length, $char[0], STR_PAD_LEFT));
    }

    public function padRight(int $length, string $char = ' ')
    {
        if (strlen($char) === 0) {
            return $this;
        }

        return String(str_pad($this->value, $length, $char[0], STR_PAD_RIGHT));
    }

    public function explode(string $delimiter): ArrayList
    {
        return new ArrayList(explode($delimiter, $this->value));
    }

    public function split(int $splitLength = 1): ArrayList
    {
        return new ArrayList(str_split($this->value, $splitLength));
    }
}
