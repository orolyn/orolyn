<?php

namespace Orolyn\Net\Http;

use Orolyn\Collection\ICollection;
use Traversable;

class Header implements ICollection
{
    private string $name;
    private array $values;

    /**
     * @param string $name
     * @param string|array $value
     */
    public function __construct(string $name, string|array $value)
    {
        $this->name = Header::normalizeName($name);;
        $this->values = is_string($value) ? [$value] : $value;
    }

    /**
     * @param Header $original
     * @param Header $addition
     * @return Header
     */
    public static function mergeHeaders(Header $original, Header $addition): Header
    {
        return new Header($original->getName(), array_merge($original->values, $addition->values));
    }

    /**
     * @param string $name
     * @return string
     */
    public static function normalizeName(string $name): string
    {
        return trim(strtolower($name));
    }

    /**
     * @param string $name
     * @return string
     */
    public static function cannonicalizeName(string $name): string
    {
        return implode('-', array_map('ucfirst', explode('-', $name)));
    }

    /**
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        foreach ($this->values as $value) {
            yield $value;
        }
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->values);
    }

    /**
     * @inheritdoc
     */
    public function isEmpty(): bool
    {
        return 0 === count($this->values);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->values[0];
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getValue();
    }
}
