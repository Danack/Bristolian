<?php

namespace BristolianTest\Middleware;

use Bristolian\Middleware\AppSessionMiddleware;
use BristolianTest\BaseTestCase;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Bristolian\Session\FakeAppSessionManager;

/**
 * @covers \Bristolian\Middleware\AppSessionMiddleware
 */
class AppSessionMiddlewareTest extends BaseTestCase
{
    public function testWorks()
    {
        $header_name = "someName";
        $header_value = "some value";


        $testing = [[$header_name, $header_value]];


//        $appSessionManager = new FakeAppSessionManager([[$header_name, $header_value]]);
        $appSessionManager = new FakeAppSessionManager($testing);


        $middleware = new AppSessionMiddleware($appSessionManager);

        $request = new ServerRequest();
        $request_handler = new class() implements RequestHandler {
            public function __construct()
            {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };

        $result = $middleware->process($request, $request_handler);

        $this->assertInstanceOf(ResponseInterface::class, $result);

        $this->assertTrue($result->hasHeader($header_name));
        $headers = $result->getHeader($header_name);

        $this->assertCount(1, $headers);
        $this->assertSame($header_value, $headers[0]);
    }

    public function testWorks_with_x_session_renew_header()
    {
        $header_name = "set-cookie";
        $header_value = "session_id=abc123; path=/";

        // Create a fake that returns empty array from saveIfOpenedAndGetHeaders
        // but returns headers from renewSession
        $appSessionManager = new class($header_name, $header_value) extends FakeAppSessionManager {
            public function __construct(
                private string $header_name,
                private string $header_value
            ) {
                // Pass null to parent so fake_headers is null, not empty array
                // This makes saveIfOpenedAndGetHeaders return default headers
                // But we'll override both methods
                parent::__construct(null);
            }

            public function saveIfOpenedAndGetHeaders(): array
            {
                // Return empty array to trigger the x-session-renew path
                return [];
            }

            public function renewSession(): array
            {
                return [[$this->header_name, $this->header_value]];
            }
        };

        $middleware = new AppSessionMiddleware($appSessionManager);

        $request = new ServerRequest();
        $request = $request->withHeader('x-session-renew', 'true');
        $request_handler = new class() implements RequestHandler {
            public function __construct()
            {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };

        $result = $middleware->process($request, $request_handler);

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertTrue($result->hasHeader($header_name));
        $headers = $result->getHeader($header_name);
        // Check that our header value is in the headers array
        $this->assertContains($header_value, $headers, "Expected header value '$header_value' not found in headers: " . json_encode($headers));
    }
}
