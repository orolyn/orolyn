<?php

namespace Orolyn\Data;

/**
 * @internal
 */
interface IDataObjectDriver
{
    public function exec(string $statement): int|false;
}
