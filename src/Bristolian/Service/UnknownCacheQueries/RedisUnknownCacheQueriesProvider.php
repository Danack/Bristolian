<?php

declare(strict_types=1);

namespace Bristolian\Service\UnknownCacheQueries;

use Bristolian\Keys\UnknownCacheQueryKey;

final class RedisUnknownCacheQueriesProvider implements UnknownCacheQueriesProvider
{
    public function __construct(
        private readonly \Redis $redis
    ) {
    }

    public function getMemberKeys(): array|false
    {
        return $this->redis->sMembers(UnknownCacheQueryKey::SET_KEY);
    }

    public function getQuery(string $key): string|false
    {
        return $this->redis->get($key);
    }
}
