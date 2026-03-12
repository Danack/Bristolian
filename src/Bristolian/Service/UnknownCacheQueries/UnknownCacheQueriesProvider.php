<?php

declare(strict_types=1);

namespace Bristolian\Service\UnknownCacheQueries;

/**
 * Provides access to unknown cache query keys and their stored query text.
 * Used by the admin page to list queries that were not recognised by the cache tag mapping.
 */
interface UnknownCacheQueriesProvider
{
    /**
     * Get the set of member keys (e.g. from Redis SMEMBERS).
     *
     * @return array<int, string>|false List of key strings, or false on failure
     */
    public function getMemberKeys(): array|false;

    /**
     * Get the stored query string for a key (e.g. from Redis GET).
     *
     * @return string|false The query text, or false if not found / error
     */
    public function getQuery(string $key): string|false;
}
