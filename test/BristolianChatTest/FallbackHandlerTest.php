<?php

declare(strict_types=1);

namespace BristolianChatTest;

use Amp\Http\HttpStatus;
use Amp\Http\Server\Request;
use BristolianChat\FallbackHandler;
use BristolianChatTest\Fixtures\FakeHttpClient;
use BristolianChatTest\Fixtures\Psr7UriForTests;
use BristolianTest\BaseTestCase;

/**
 * @covers \BristolianChat\FallbackHandler
 */
class FallbackHandlerTest extends BaseTestCase
{
    public function test_handleRequest_returns_200_with_expected_body(): void
    {
        $handler = new FallbackHandler();
        $httpClient = new FakeHttpClient(1, '127.0.0.1:9999', '0.0.0.0:80');
        $uri = Psr7UriForTests::fromString('http://localhost/');
        $request = new Request($httpClient, 'GET', $uri, [], '', '1.1', null);

        $response = $handler->handleRequest($request);

        $this->assertSame(HttpStatus::OK, $response->getStatus());
        $body = '';
        while (($chunk = $response->getBody()->read()) !== null) {
            $body .= $chunk;
        }
        $this->assertSame(
            'This is a default response. Maybe try a specific end-point...',
            $body
        );
    }
}
