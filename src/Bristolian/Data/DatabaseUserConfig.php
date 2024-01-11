<?php

declare(strict_types = 1);

namespace Bristolian\Data;

/**
 * @codeCoverageIgnore
 */
class DatabaseUserConfig
{
    public function __construct(
        readonly string $host,
        readonly string $username,
        readonly string $password,
        readonly string $schema,
    ) {
    }
}
