<?php

namespace Orolyn\Data;

use PDO;

interface IDataObject
{
    public function prepare(string $query, array $options = []): IDataObjectStatement|false;

    public function beginTransaction(): bool;

    public function commit(): bool;

    public function rollBack(): bool;

    public function inTransaction(): bool;

    public function setAttribute(
        int $attribute,
        mixed $value
    ): bool;

    public function exec(string $statement): int|false;

    public function query($statement, $mode = PDO::ATTR_DEFAULT_FETCH_MODE, ...$fetch_mode_args): void;

    public function lastInsertId(?string $name = null): string|false;

    public function errorCode(): ?string;

    public function errorInfo(): array;

    public function getAttribute(int $attribute): mixed;

    public function quote(
        string $string,
        int $type = PDO::PARAM_STR
    ): string|false;

    public static function getAvailableDrivers(): array;
}
