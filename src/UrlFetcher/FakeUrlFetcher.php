<?php

declare(strict_types = 1);

namespace UrlFetcher;

/**
 * Fetches the contents of a URL and returns it as a string.
 */
class FakeUrlFetcher implements UrlFetcher
{
    /**
     * @var int Number of times called
     */
    private int $hits = 0;

    public function __construct(public readonly string $data)
    {
    }

    /**
     * @param string $uri
     * @return string
     * @throws UrlNotOkException
     */
    public function getUrl(string $uri): string
    {
        $this->hits += 1;
        return $this->data;
    }

    /**
     * @return int
     */
    public function getHits(): int
    {
        return $this->hits;
    }
}
