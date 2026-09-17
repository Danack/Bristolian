<?php

declare(strict_types = 1);

namespace UrlFetcherTest;

use BristolianTest\BaseTestCase;
use UrlFetcher\CurlUrlFetcher;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\UrlFetcher\CurlUrlFetcher::class)]

class CurlUrlFetcherTest extends BaseTestCase
{
    /**
     * @group network
     */
    public function testBasic(): void
    {
        $urlFetcher = new CurlUrlFetcher();
        $result = $urlFetcher->getUrl('http://www.google.com');
        $this->assertStringStartsWith(
            "<!doctype html>",
            $result
        );
    }
}
