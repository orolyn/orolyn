<?php

namespace Orolyn;

class Version implements IEquatable, IComparable
{
    public readonly int $major;
    public readonly int $minor;
    public readonly int $patch;

    private ?string $toString = null;

    public function __construct(
        ?int $major = null,
        ?int $minor = null,
        ?int $patch = null,
    ) {
        $this->major = $major ?? 0;
        $this->minor = $minor ?? 0;
        $this->patch = $patch ?? 0;
    }

    public static function parse(string $input): Version
    {
        if (
            !Regex::create('^(\d+)(?:\.(\d+))?(?:\.(\d+))?')->match(
                $input,
                $match,
                $major,
                $minor,
                $patch,
            )
        ) {
            throw new FormatException('Invalid version format');
        }

        return new Version(
            $major,
            $minor,
            $patch,
        );
    }

    public function getHashCode(): int
    {
        $accumulator = 0;

        $accumulator |= ($this->major & 0x0000000F) << 28;
        $accumulator |= ($this->minor & 0x000000FF) << 20;
        $accumulator |= ($this->patch & 0x000000FF) << 12;

        return $accumulator;
    }

    public function equals(mixed $value): bool
    {
        if (!$value instanceof self) {
            return false;
        }

        if ($this === $value) {
            return true;
        }

        return $value->major === $this->major && $value->minor === $this->minor && $value->patch === $this->patch;
    }

    public function compareTo(mixed $value): int
    {
        return
            !$value instanceof self ? 1 : (
            $this === $value ? 0 : (
            $this->major <=> $value->major ?: (
            $this->minor <=> $value->minor ?: (
                $this->patch <=> $value->patch
            )
            )
            )
            );
    }

    public function __toString(): string
    {
        return $this->toString ?? $this->toString = sprintf(
            '%s.%s.%s',
            $this->major,
            $this->minor,
            $this->patch,
        );
    }
}
