<?php

declare(strict_types = 1);

namespace Bristolian\Data;

class DatabaseRootConfig
{
    public function __construct(
        readonly string $username,
        readonly string $database,
        readonly string $password
    ) {
    }
}
