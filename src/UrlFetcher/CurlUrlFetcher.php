<?php

declare(strict_types = 1);

namespace UrlFetcher;

class CurlUrlFetcher implements UrlFetcher
{
    public function getUrl(string $uri): string
    {
        [$statusCode, $body, $headers] = \fetchUri(
            $uri,
            $method = 'GET',
            $queryParams = [],
            $body = null,
            $headers = []
        );

        if ($statusCode !== 200) {
            throw UrlFetcherException::notOk($statusCode, $uri);
        }

        return $body;
    }
}
