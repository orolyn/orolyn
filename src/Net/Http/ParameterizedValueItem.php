<?php

namespace Orolyn\Net\Http;

use Orolyn\Collection\ICollection;
use Orolyn\Collection\IList;

class ParameterizedValueItem
{
    /**
     * @param string $value
     * @param IList<ParameterizedValueItemParameter> $parameters
     */
    public function __construct(
        private string $value,
        private IList $parameters
    ) {
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return IList<ParameterizedValueItemParameter>
     */
    public function getParameters(): IList
    {
        return $this->parameters;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $string = $this->value;

        if ($this->parameters->count() > 0) {
            $string .= '; ' . $this->parameters->join('; ');
        }

        return $string;
    }
}
