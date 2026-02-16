<?php

namespace BristolianTest\Service\HttpFetcher;

use Bristolian\Service\HttpFetcher\FakeHttpFetcherWithFixedResponse;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class FakeHttpFetcherWithFixedResponseTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\HttpFetcher\FakeHttpFetcherWithFixedResponse::fetch
     */
    public function testFetchReturnsFixedResponseRegardlessOfRequest(): void
    {
        $fetcher = new FakeHttpFetcherWithFixedResponse(200, '<html>body</html>', ['X-Custom: value']);

        [$statusCode, $body, $headers] = $fetcher->fetch('https://example.com/other', 'POST', ['a' => 'b'], 'postbody');

        $this->assertSame(200, $statusCode);
        $this->assertSame('<html>body</html>', $body);
        $this->assertSame(['X-Custom: value'], $headers);
    }
}
