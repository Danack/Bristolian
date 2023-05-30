<?php

declare(strict_types = 1);

namespace UrlFetcher;

/**
 * Fetches the contents of a URL and returns it as a string.
 */
interface UrlFetcher
{
    /**
     * @param string $uri
     * @return string
     * @throws UrlNotOkException
     */
    public function getUrl(string $uri): string;
}
