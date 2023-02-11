<?php

namespace Orolyn\Data\Mysql\Protocol\Session;

use Exception;
use Orolyn\Collection\Dictionary;
use Orolyn\Collection\ICollection;
use Orolyn\Collection\IDictionary;
use Orolyn\Data\Mysql\Protocol\LengthEncoded;
use Orolyn\IO\ByteStream;
use Traversable;

class SessionTrackSystemVariables implements ICollection
{
    public function __construct(
        private array $data
    ) {
    }

    public function get(string $variableName): ?string
    {
        if (!array_key_exists($variableName, $this->data)) {
            return null;
        }

        return $this->data[$variableName];
    }

    public function getIterator(): Traversable
    {
        foreach ($this->data as $name => $value) {
            yield $name => $value;
        }
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function isEmpty(): bool
    {
        return 0 === $this->count();
    }

    public static function decode(ByteStream $stream): SessionTrackSystemVariables
    {
        $data = [];

        while ($stream->getBytesAvailable() > 0) {
            $data[LengthEncoded::decodeLengthEncodedString($stream)] =
                LengthEncoded::decodeLengthEncodedString($stream);
        }

        return new SessionTrackSystemVariables($data);
    }
}
