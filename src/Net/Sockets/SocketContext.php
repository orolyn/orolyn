<?php

namespace Orolyn\Net\Sockets;

use Orolyn\ArgumentException;
use Orolyn\Collection\Dictionary;
use Orolyn\Net\Sockets\Options\Options;
use function Orolyn\Lang\ClassInstanceOf;

class SocketContext
{
    /**
     * @var Dictionary
     */
    private Dictionary $options;

    /**
     * @param Options ...$optionsList
     */
    public function __construct(Options ...$optionsList)
    {
        $this->options = new Dictionary();

        foreach ($optionsList as $options) {
            $this->options->add(get_class($options), $options);
        }
    }

    /**
     * @template T
     * @param class-string<T> $className
     * @return T|null
     */
    public function getOptions(string $className): ?Options
    {
        if (!ClassInstanceOf($className, Options::class)) {
            throw new ArgumentException('Invalid options class name.');
        }

        if ($this->options->try($className, $options)) {
            return $options;
        }

        return new Options();
    }
}
