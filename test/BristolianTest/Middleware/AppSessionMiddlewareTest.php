<?php

namespace BristolianTest\Middleware;

use Bristolian\BristolianException;
use Bristolian\Middleware\AppSessionMiddleware;

use Asm\RequestSessionStorage;
use BristolianTest\BaseTestCase;
use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Laminas\Diactoros\Response;

/**
 * @covers \Bristolian\Middleware\AppSessionMiddleware
 */
class AppSessionMiddlewareTest extends BaseTestCase
{

    public function testWorks()
    {
        $header_name = "someName";
        $header_value = "some value";

        $session = new FakeSession([
            [$header_name, $header_value],
//            [$header_name_2, $header_value],
        ]);
        $sessionStorage = new FakeRequestSessionStorage($session);
        $middleware = new AppSessionMiddleware($sessionStorage);

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
