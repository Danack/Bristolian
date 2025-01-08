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

        $appSessionManager = new FakeAppSessionManager([[$header_name, $header_value]]);

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
}
