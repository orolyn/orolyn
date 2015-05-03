<?php

namespace Orolyn\Net\Sockets\Options;

use Orolyn\Collection\ICollection;

class Options implements ICollection
{
    protected array $options = [];

    /**
     * @return static
     */
    public static function create(): static
    {
        return new static();
    }

    public function getIterator(): \Generator
    {
        foreach ($this->options as $name => $value) {
            yield $name => $value;
        }
    }

    public function count(): int
    {
        return count($this->options);
    }

    protected function get(string $option): mixed
    {
        if (!array_key_exists($option, $this->options)) {
            return null;
        }

        return $this->options[$option];
    }

    protected function set(string $option, mixed $value): static
    {
        if (null === $value) {
            unset($this->options[$option]);
        } else {
            $this->options[$option] = $value;
        }

        return $this;
    }
}
