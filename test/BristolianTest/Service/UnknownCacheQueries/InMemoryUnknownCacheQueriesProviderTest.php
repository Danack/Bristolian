<?php

declare(strict_types=1);

namespace BristolianTest\Service\UnknownCacheQueries;

use Bristolian\Service\UnknownCacheQueries\InMemoryUnknownCacheQueriesProvider;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\UnknownCacheQueries\InMemoryUnknownCacheQueriesProvider::class, 'addKey')]
#[CoversMethod(\Bristolian\Service\UnknownCacheQueries\InMemoryUnknownCacheQueriesProvider::class, 'getMemberKeys')]
#[CoversMethod(\Bristolian\Service\UnknownCacheQueries\InMemoryUnknownCacheQueriesProvider::class, 'getQuery')]
#[CoversMethod(\Bristolian\Service\UnknownCacheQueries\InMemoryUnknownCacheQueriesProvider::class, 'setQuery')]

class InMemoryUnknownCacheQueriesProviderTest extends BaseTestCase
{
    public function test_getMemberKeys_returns_added_keys(): void
    {
        $provider = new InMemoryUnknownCacheQueriesProvider();
        $provider->addKey('key1');
        $provider->addKey('key2');
        $this->assertSame(['key1', 'key2'], $provider->getMemberKeys());
    }

    public function test_getQuery_returns_set_query_or_false(): void
    {
        $provider = new InMemoryUnknownCacheQueriesProvider();
        $this->assertFalse($provider->getQuery('missing'));

        $provider->setQuery('key1', 'SELECT * FROM users');
        $this->assertSame('SELECT * FROM users', $provider->getQuery('key1'));
        $this->assertFalse($provider->getQuery('key2'));
    }
}
