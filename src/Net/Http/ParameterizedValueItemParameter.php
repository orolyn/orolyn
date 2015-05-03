<?php

namespace Orolyn\Net\Http;

class ParameterizedValueItemParameter
{
    /**
     * @var string
     */
    private string $key;

    /**
     * @var string|null
     */
    private ?string $value;

    /**
     * @param string $key
     * @param string|null $value
     */
    public function __construct(string $key, ?string $value = null)
    {
        $this->key = trim($key);
        $this->value = $value ? trim($value) : null;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->key . ($this->value ? '=' . $this->value : '');
    }
}
