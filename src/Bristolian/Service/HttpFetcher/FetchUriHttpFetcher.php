<?php

namespace Bristolian\Service\HttpFetcher;

use function fetchUri;

class FetchUriHttpFetcher implements HttpFetcher
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
        return fetchUri($uri, $method, $queryParams, $body, $headers);
    }
}
