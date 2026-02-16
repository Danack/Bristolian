<?php

namespace Bristolian\Service\HttpFetcher;

/**
 * Fetches a URI and returns status code, body, and response headers.
 * Return format matches fetchUri(): array{0: int, 1: string, 2: array<string, string|string[]>}
 */
interface HttpFetcher
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
    ): array;
}
