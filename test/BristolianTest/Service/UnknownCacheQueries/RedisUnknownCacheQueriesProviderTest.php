<?php

declare(strict_types=1);

namespace BristolianTest\Service\UnknownCacheQueries;

use Bristolian\Keys\UnknownCacheQueryKey;
use Bristolian\Service\UnknownCacheQueries\RedisUnknownCacheQueriesProvider;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 * @group db
 */
class RedisUnknownCacheQueriesProviderTest extends BaseTestCase
{
    private \Redis $redis;

    public function setup(): void
    {
        parent::setup();
        $this->redis = $this->injector->make(\Redis::class);
    }

    private function cleanupKey(string $absoluteKey): void
    {
        $this->redis->del($absoluteKey);
        $this->redis->sRem(UnknownCacheQueryKey::SET_KEY, $absoluteKey);
    }

    /**
     * @covers \Bristolian\Service\UnknownCacheQueries\RedisUnknownCacheQueriesProvider::__construct
     * @covers \Bristolian\Service\UnknownCacheQueries\RedisUnknownCacheQueriesProvider::getMemberKeys
     * @covers \Bristolian\Service\UnknownCacheQueries\RedisUnknownCacheQueriesProvider::getQuery
     */
    public function test_getMemberKeys_and_getQuery_return_seeded_data(): void
    {
        $query = 'SELECT test_provider_' . time() . '_' . random_int(1000, 9999);
        $absoluteKey = UnknownCacheQueryKey::getAbsoluteKeyName($query);
        $this->cleanupKey($absoluteKey);

        $this->redis->sAdd(UnknownCacheQueryKey::SET_KEY, $absoluteKey);
        $this->redis->set($absoluteKey, $query);

        $provider = new RedisUnknownCacheQueriesProvider($this->redis);

        $memberKeys = $provider->getMemberKeys();
        $this->assertIsArray($memberKeys);
        $this->assertContains($absoluteKey, $memberKeys);

        $this->assertSame($query, $provider->getQuery($absoluteKey));

        $this->cleanupKey($absoluteKey);
    }

    /**
     * @covers \Bristolian\Service\UnknownCacheQueries\RedisUnknownCacheQueriesProvider::getQuery
     */
    public function test_getQuery_returns_false_for_missing_key(): void
    {
        $provider = new RedisUnknownCacheQueriesProvider($this->redis);
        $this->assertFalse($provider->getQuery('nonexistent_key_that_does_not_exist'));
    }
}
