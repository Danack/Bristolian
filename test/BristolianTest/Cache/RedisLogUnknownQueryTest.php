<?php

declare(strict_types=1);

namespace BristolianTest\Cache;

use Bristolian\Cache\RedisLogUnknownQuery;
use Bristolian\Keys\UnknownCacheQueryKey;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Cache\RedisLogUnknownQuery
 * @group db
 */
class RedisLogUnknownQueryTest extends BaseTestCase
{
    private \Redis $redis;

    public function setup(): void
    {
        parent::setup();
        $this->redis = $this->injector->make(\Redis::class);
    }

    private function cleanupKeys(string $query): void
    {
        $key = UnknownCacheQueryKey::getAbsoluteKeyName($query);
        $this->redis->del($key);
        $this->redis->sRem(UnknownCacheQueryKey::SET_KEY, $key);
    }

    public function testHandleStoresQueryInRedis(): void
    {
        $query = 'SELECT test_unknown_' . time() . '_' . random_int(1000, 9999);
        $this->cleanupKeys($query);

        $handler = new RedisLogUnknownQuery($this->redis);
        $handler->handle($query);

        $key = UnknownCacheQueryKey::getAbsoluteKeyName($query);

        $storedQuery = $this->redis->get($key);
        $this->assertSame($query, $storedQuery);

        $isMember = $this->redis->sIsMember(UnknownCacheQueryKey::SET_KEY, $key);
        $this->assertTrue($isMember);

        $this->cleanupKeys($query);
    }

    public function testHandleAddsKeyToSet(): void
    {
        $query = 'INSERT test_unknown_set_' . time() . '_' . random_int(1000, 9999);
        $this->cleanupKeys($query);

        $handler = new RedisLogUnknownQuery($this->redis);
        $handler->handle($query);

        $key = UnknownCacheQueryKey::getAbsoluteKeyName($query);
        $members = $this->redis->sMembers(UnknownCacheQueryKey::SET_KEY);
        $this->assertContains($key, $members);

        $this->cleanupKeys($query);
    }

    public function testHandleSameQueryTwiceDoesNotDuplicate(): void
    {
        $query = 'SELECT test_idempotent_' . time() . '_' . random_int(1000, 9999);
        $this->cleanupKeys($query);

        $handler = new RedisLogUnknownQuery($this->redis);
        $handler->handle($query);
        $handler->handle($query);

        $key = UnknownCacheQueryKey::getAbsoluteKeyName($query);
        $storedQuery = $this->redis->get($key);
        $this->assertSame($query, $storedQuery);

        $this->cleanupKeys($query);
    }
}
