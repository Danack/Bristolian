<?php

namespace Bristolian\Service\HttpFetcher;

/**
 * Test double that returns a fixed status code, body and headers regardless of request.
 * Use in tests to drive BccTroFetcher (or other HTTP-dependent code) with known responses.
 */
class FakeHttpFetcherWithFixedResponse implements HttpFetcher
{
    /** @var int */
    private $statusCode;
    /** @var string */
    private $body;
    /** @var mixed[] */
    private $headers;

    /**
     * @param array<string, mixed>|array<int, string> $headers
     */
    public function __construct(int $statusCode, string $body, array $headers = [])
    {
        $this->statusCode = $statusCode;
        $this->body = $body;
        $this->headers = $headers;
    }

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
        return [$this->statusCode, $this->body, $this->headers];
    }
}
