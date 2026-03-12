<?php

declare(strict_types=1);

namespace Bristolian\Service\UnknownCacheQueries;

/**
 * In-memory implementation for tests. Seed keys and query text via addKey/addQuery
 * to drive all branches of Admin::showUnknownCacheQueries.
 */
final class InMemoryUnknownCacheQueriesProvider implements UnknownCacheQueriesProvider
{
    /** @var array<int, string> */
    private array $keys = [];

    /** @var array<string, string> key => query text (missing key => getQuery returns false) */
    private array $queries = [];

    public function addKey(string $key): void
    {
        $this->keys[] = $key;
    }

    /**
     * Set the query text for a key. If not set, getQuery($key) will return false.
     */
    public function setQuery(string $key, string $query): void
    {
        $this->queries[$key] = $query;
    }

    /**
     * @return array<int, string>
     */
    public function getMemberKeys(): array
    {
        return $this->keys;
    }

    public function getQuery(string $key): string|false
    {
        return $this->queries[$key] ?? false;
    }
}
