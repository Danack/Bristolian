<?php

namespace Bristolian\Service\HttpFetcher;

/**
 * Test double that always returns HTTP 404 with empty body.
 * Use in tests to simulate fetch failures without hitting the network.
 */
class FakeHttpFetcherReturning404 implements HttpFetcher
{
    /**
     * @param array<string, mixed> $queryParams
     * @param array<int, string> $headers
     * @return array{0: int, 1: string, 2: mixed[]}
     */
    public function fetch(
        string $uri,
        string $method = 'GET',
        array $queryParams = [],
        string|null $body = null,
        array $headers = []
    ): array {
        return [404, '', []];
    }
}
