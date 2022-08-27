<?php

namespace Orolyn\Data;

use Iterator;
use IteratorAggregate;
use PDO;
use stdClass;

class AsynchronousDataObjectStatement implements IDataObjectStatement
{
    public readonly string $queryString;

    public function execute(?array $params = null): bool {}

    public function fetch(
        int $mode = PDO::FETCH_BOTH,
        int $cursorOrientation = PDO::FETCH_ORI_NEXT,
        int $cursorOffset = 0
    ): mixed {}

    public function bindParam(
        int|string $param,
        mixed &$var,
        int $type = PDO::PARAM_STR,
        int $maxLength = null,
        mixed $driverOptions = null
    ): bool {}

    public function bindColumn(
        int|string $param,
        mixed &$var,
        int $type = PDO::PARAM_STR,
        int $maxLength = null,
        mixed $driverOptions = null
    ): bool {}

    public function bindValue(
        int|string $param,
        mixed $value,
        int $type = PDO::PARAM_STR
    ): bool {}

    public function rowCount(): int {}

    public function fetchColumn(int $column = 0): mixed {}

    public function fetchAll(
        int $mode = PDO::FETCH_BOTH,
        mixed ...$args
    ): array {}

    public function fetchObject(string|null $class = stdClass::class, array $constructorArgs = []): object|false {}

    public function errorCode(): ?string {}

    public function errorInfo(): array {}

    public function setAttribute(
        int $attribute,
        mixed $value
    ): bool {}

    public function getAttribute(int $name): mixed {}

    public function columnCount(): int {}

    public function getColumnMeta(int $column): array|false {}

    public function setFetchMode(int $mode, $className = null, mixed ...$params) {}

    public function nextRowset(): bool {}

    public function closeCursor(): bool {}

    public function debugDumpParams(): ?bool {}

    final public function __wakeup() {}

    final public function __sleep() {}

    public function getIterator(): Iterator {}
}
