<?php

namespace Orolyn;

use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use Orolyn\Collection\ArrayList;
use Orolyn\Collection\EmptyList;
use Orolyn\Collection\IList;
use Orolyn\Collection\StaticList;
use Throwable;

class AggregateException extends RuntimeException
{
    /**
     * @var IList<Throwable>
     */
    private IList $exceptions;

    /**
     * @param Throwable|null $previous
     * @param IList<Throwable>|null $exceptions
     * @param string $message
     */
    public function __construct(Throwable $previous = null, ?IList $exceptions = null, string $message = "")
    {
        parent::__construct($message, 0, $previous);

        $this->exceptions = $exceptions ?? StaticList::createImmutableList($previous ? [$previous] : []);
    }

    /**
     * @return IList<Throwable>
     */
    public function getExceptions(): IList
    {
        return clone $this->exceptions;
    }

    /**
     * @return AggregateException
     */
    public function flatten(): AggregateException
    {
        $innerExceptions = [];

        foreach ($this->exceptions as $innerException) {
            if ($innerException instanceof AggregateException) {
                foreach ($innerException->flatten()->exceptions as $innerInnerException) {
                    $innerExceptions[] = $innerInnerException;
                }
            } else {
                $innerExceptions[] = $innerException;
            }
        }

        return new AggregateException(null, StaticList::createImmutableList($innerExceptions));
    }
}
