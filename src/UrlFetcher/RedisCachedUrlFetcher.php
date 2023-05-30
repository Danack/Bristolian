<?php

declare(strict_types = 1);

namespace UrlFetcher;

use Key\UrlCacheKey;

class RedisCachedUrlFetcher implements UrlFetcher
{
    private \Redis $redis;

    private CurlUrlFetcher $urlFetcher;

    /**
     * RedisCachedUrlFetcher constructor.
     * @param \Redis $redis
     * @param CurlUrlFetcher $urlFetcher
     */
    public function __construct(\Redis $redis, CurlUrlFetcher $urlFetcher)
    {
        $this->redis = $redis;
        $this->urlFetcher = $urlFetcher;
    }

    public function getUrl(string $uri): string
    {
        $cacheKey = UrlCacheKey::getAbsoluteKeyName($uri);

        $cached = $this->redis->get($cacheKey);
        if ($cached !== false) {
            return $cached;
        }
        $data = $this->urlFetcher->getUrl($uri);

        // cache for one hour
        $this->redis->set($cacheKey, $data, 3600);

        return $data;
    }
}
