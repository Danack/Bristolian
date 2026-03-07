<?php

declare(strict_types=1);

namespace Bristolian\Cache;

interface UnknownQueryHandler
{
    public function handle(string $query): void;
}
