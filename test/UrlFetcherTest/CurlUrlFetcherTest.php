<?php

declare(strict_types = 1);

namespace UrlFetcherTest;

use BristolianTest\BaseTestCase;
use UrlFetcher\CurlUrlFetcher;

/**
 * @coversNothing
 */
class CurlUrlFetcherTest extends BaseTestCase
{
    /**
     * @covers \UrlFetcher\CurlUrlFetcher
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
