<?php

namespace BristolianTest\Service\HttpFetcher;

use Bristolian\Service\HttpFetcher\FakeHttpFetcherReturning404;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\HttpFetcher\FakeHttpFetcherReturning404::class, 'fetch')]

class FakeHttpFetcherReturning404Test extends BaseTestCase
{
    public function testFetchReturns404WithEmptyBody(): void
    {
        $fetcher = new FakeHttpFetcherReturning404();

        [$statusCode, $body, $headers] = $fetcher->fetch('https://example.com/any', 'GET');

        $this->assertSame(404, $statusCode);
        $this->assertSame('', $body);
        $this->assertSame([], $headers);
    }
}
