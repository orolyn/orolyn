<?php

namespace Orolyn\Data;

use Orolyn\Data\Mysql\Mysql;
use Orolyn\Net\Uri;
use PDO;

class AsynchronousDataObject implements IDataObject
{
    private IDataObjectDriver $driver;

    public function __construct(string $dsn, string $username = null, string $password = null, array $options = null)
    {
        $uri = Uri::parseUri($dsn);

        if ($username) {
            $uri->setUserInfo($username, $password);
        }

        $this->driver = match($uri->getScheme()) {
            'mysql' => new Mysql($uri)
        };
    }

    public function prepare(string $query, array $options = []): IDataObjectStatement|false
    {
        return new AsynchronousDataObjectStatement();
    }

    public function beginTransaction(): bool
    {
        // TODO: Implement beginTransaction() method.
    }

    public function commit(): bool
    {
        // TODO: Implement commit() method.
    }

    public function rollBack(): bool
    {
        // TODO: Implement rollBack() method.
    }

    public function inTransaction(): bool
    {
        // TODO: Implement inTransaction() method.
    }

    public function setAttribute(int $attribute, mixed $value): bool
    {
        // TODO: Implement setAttribute() method.
    }

    public function exec(string $statement): int|false
    {
        // TODO: Implement exec() method.
    }

    public function query($statement, $mode = PDO::ATTR_DEFAULT_FETCH_MODE, ...$fetch_mode_args): void
    {
        // TODO: Implement query() method.
    }

    public function lastInsertId(?string $name = null): string|false
    {
        // TODO: Implement lastInsertId() method.
    }

    public function errorCode(): ?string
    {
        // TODO: Implement errorCode() method.
    }

    public function errorInfo(): array
    {
        // TODO: Implement errorInfo() method.
    }

    public function getAttribute(int $attribute): mixed
    {
        // TODO: Implement getAttribute() method.
    }

    public function quote(string $string, int $type = PDO::PARAM_STR): string|false
    {
        // TODO: Implement quote() method.
    }

    public static function getAvailableDrivers(): array
    {
        // TODO: Implement getAvailableDrivers() method.
    }


}
