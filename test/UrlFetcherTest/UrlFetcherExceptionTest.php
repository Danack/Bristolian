<?php

declare(strict_types = 1);

namespace UrlFetcherTest;

use BristolianTest\BaseTestCase;
use UrlFetcher\UrlFetcherException;

/**
 * @coversNothing
 */
class UrlFetcherExceptionTest extends BaseTestCase
{
    /**
     * @covers \UrlFetcher\UrlFetcherException
     */
    public function testBasic(): void
    {
        $status = 503;
        $url = "http://www.example.com";

        $exception = UrlFetcherException::notOk($status, $url);

        $this->assertSame($status, $exception->getStatusCode());
        $this->assertSame($url, $exception->getUri());
    }
}
