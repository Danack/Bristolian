<?php

declare(strict_types=1);

namespace Bristolian\Cache;

use Bristolian\Keys\UnknownCacheQueryKey;

class RedisLogUnknownQuery implements UnknownQueryHandler
{
    public function __construct(private \Redis $redis)
    {
    }

    public function handle(string $query): void
    {
        $key = UnknownCacheQueryKey::getAbsoluteKeyName($query);
        $this->redis->sAdd(UnknownCacheQueryKey::SET_KEY, $key);
        $this->redis->set($key, $query);
    }
}
