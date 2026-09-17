<?php

namespace BristolianTest\Service\HttpFetcher;

use Bristolian\Service\HttpFetcher\FakeHttpFetcherWithFixedResponse;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\HttpFetcher\FakeHttpFetcherWithFixedResponse::class, '__construct')]
#[CoversMethod(\Bristolian\Service\HttpFetcher\FakeHttpFetcherWithFixedResponse::class, 'fetch')]

class FakeHttpFetcherWithFixedResponseTest extends BaseTestCase
{
    public function testFetchReturnsFixedResponseRegardlessOfRequest(): void
    {
        $fetcher = new FakeHttpFetcherWithFixedResponse(200, '<html>body</html>', ['X-Custom: value']);

        [$statusCode, $body, $headers] = $fetcher->fetch('https://example.com/other', 'POST', ['a' => 'b'], 'postbody');

        $this->assertSame(200, $statusCode);
        $this->assertSame('<html>body</html>', $body);
        $this->assertSame(['X-Custom: value'], $headers);
    }
}
