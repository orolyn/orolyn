<?php
namespace Orolyn\Collection;

use Ds\Hashable;
use Ds\Set;
use Orolyn\IEquatable;
use SplObjectStorage;

/**
 * @internal
 */
class Item implements Hashable
{
    public function __construct(
        public readonly mixed $value
    ) {
    }

    public static function getArray(iterable $values): array
    {
        $array = [];

        foreach ($values as $value) {
            $array[] = new Item($value);
        }

        return $array;
    }

    /**
     * @inheritDoc
     */
    public function equals($obj): bool
    {
        $obj = $obj instanceof Item ? $obj->value : $obj;

        if ($this->value instanceof IEquatable) {
            return $this->value->equals($obj);
        }

        return $this->value === $obj;
    }

    /**
     * @inheritDoc
     */
    public function hash()
    {
        if ($this->value instanceof IEquatable) {
            return $this->value->getHashCode();
        }

        if (is_object($this->value)) {
            return spl_object_id($this->value);
        }

        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
