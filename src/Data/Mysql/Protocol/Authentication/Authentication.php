<?php

namespace Orolyn\Data\Mysql\Protocol\Authentication;

use Orolyn\Data\DriverException;
use Orolyn\Data\Mysql\Protocol\Authentication\Plugin\CachingSha2Password;

abstract class Authentication
{
    public const CACHING_SHA2_PASSWORD = 'caching_sha2_password';

    private const PLUGINS = [
        self::CACHING_SHA2_PASSWORD => CachingSha2Password::class,
    ];

    private const PLUGIN_IDS = [
        0x04 => CachingSha2Password::class,
    ];

    abstract protected function __construct(string $data);

    abstract public function getName(): string;

    abstract public function encode(string $password): string;

    public static function getPluginFromString(string $name, string $data): Authentication
    {
        if (!array_key_exists($name, self::PLUGINS)) {
            throw new DriverException('Unsupported authentication plugin');
        }

        return new (self::PLUGINS[$name])($data);
    }
}
