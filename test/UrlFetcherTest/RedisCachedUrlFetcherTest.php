<?php

declare(strict_types = 1);

namespace UrlFetcherTest;

use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;
use UrlFetcher\FakeUrlFetcher;
use UrlFetcher\RedisCachedUrlFetcher;

/**
 * @coversNothing
 * @group network
 * @group redis
 */
class RedisCachedUrlFetcherTest extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * @covers \UrlFetcher\RedisCachedUrlFetcher
     * @group network
     */
    public function testBasic(): void
    {
        $data = 'John';
        $fakeUrlFetcher = new FakeUrlFetcher($data);
        $redis = $this->injector->make(\Redis::class);
        $urlFetcher = new RedisCachedUrlFetcher(
            $redis,
            $fakeUrlFetcher
        );

        $url_unique = 'http://www.google.com?unique=' . posix_getpid() . microtime();

        $result = $urlFetcher->getUrl($url_unique);
        $this->assertSame($data, $result);
        $this->assertSame(1, $fakeUrlFetcher->getHits());

        $result = $urlFetcher->getUrl($url_unique);
        $this->assertSame($data, $result);
        $this->assertSame(1, $fakeUrlFetcher->getHits());
    }
}
