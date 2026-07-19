<?php

declare(strict_types=1);

namespace Bristolian\Cache;

use Bristolian\Cache\UnknownQueryException;

class ThrowOnUnknownQuery implements UnknownQueryHandler
{
    public function handle(string $query): void
    {
        throw UnknownQueryException::create($query);
    }
}
