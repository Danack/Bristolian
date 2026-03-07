<?php

declare(strict_types=1);

namespace Bristolian\Cache;

class ThrowOnUnknownQuery implements UnknownQueryHandler
{
    public function handle(string $query): void
    {
        throw new \RuntimeException(
            "Unknown query not in cache tag mapping: " . substr($query, 0, 200)
        );
    }
}
