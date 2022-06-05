<?php

namespace Orolyn\Net\Http;

use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use Orolyn\Collection\ICollection;
use Orolyn\Collection\IList;
use Orolyn\Collection\StaticList;
use ArrayAccess;

/**
 * @extends ICollection<ParameterizedValueItem>
 * @extends ArrayAccess<string, IList<ParameterizedValueItem>>
 */
class ParameterizedValue implements ICollection, ArrayAccess
{
    /**
     * @param string $value
     * @param IList<ParameterizedValueItem> $items
     */
    public function __construct(
        private string $value,
        private IList $items
    ) {
    }

    /**
     * @param IList<ParameterizedValueItem> $items
     * @return ParameterizedValue
     */
    public static function createFromItems(IList $items): ParameterizedValue
    {
        return new ParameterizedValue($items->join(', '), $items);
    }

    /**
     * @param string $headerValue
     * @return ParameterizedValue
     */
    public static function parse(string $headerValue): ParameterizedValue
    {
        $items = [];

        foreach (explode(',', $headerValue) as $value) {
            $components = explode(';', $value);

            $value = trim(array_shift($components));
            $parameters = [];

            foreach ($components as $parameterValue) {
                if ('' === $parameterValue = trim($parameterValue)) {
                    continue;
                }

                $parameters[] = new ParameterizedValueItemParameter(...explode('=', $parameterValue));
            }

            $items[] = new ParameterizedValueItem(
                $value,
                StaticList::createImmutableList($parameters)
            );
        }

        return new ParameterizedValue($headerValue, StaticList::createImmutableList($items));
    }

    /**
     * @inheritdoc
     */
    public function offsetExists(mixed $offset): bool
    {
        foreach ($this->items as $item) {
            if ($item->getValue() === $offset) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function offsetGet(mixed $offset): mixed
    {
        $items = [];

        foreach ($this->items as $item) {
            if ($item->getValue() === $offset) {
                $items[] = $item;
            }
        }

        return StaticList::createImmutableList($items);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset(mixed $offset): void
    {
    }

    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return $this->items->count();
    }

    /**
     * @inheritdoc
     */
    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    /**
     * @inheritdoc
     */
    public function getIterator(): \Generator
    {
        foreach ($this->items as $item) {
            yield $item;
        }
    }

    public function getParameterizedValueItems(): IList
    {
        return $this->items;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
