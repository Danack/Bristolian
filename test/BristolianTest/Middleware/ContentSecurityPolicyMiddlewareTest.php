<?php

namespace BristolianTest\Middleware;

use Bristolian\BristolianException;
use Bristolian\Data\ApiDomain;
use Bristolian\Middleware\AppSessionMiddleware;

use Asm\RequestSessionStorage;
use Bristolian\Service\RequestNonce;
use BristolianTest\BaseTestCase;
use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Laminas\Diactoros\Response;
use Bristolian\Middleware\ContentSecurityPolicyMiddleware;

/**
 * @covers \Bristolian\Middleware\ContentSecurityPolicyMiddleware
 */
class ContentSecurityPolicyMiddlewareTest extends BaseTestCase
{
    public function testWorks()
    {
        $nonce = $this->injector->make(RequestNonce::class);

        $middleware = new ContentSecurityPolicyMiddleware(
            $nonce,
            [],
            [],
            []
        );

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
    }

    /**
     * @group wip
     */
    public function testWorks_with_api_domain()
    {
        $nonce = $this->injector->make(RequestNonce::class);

        $middleware = new ContentSecurityPolicyMiddleware(
            $nonce,
            ["https://api.bristolian.org"],
            [],
            []
        );

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

        $this->assertTrue($result->hasHeader("Content-Security-Policy"));

        $header = $result->getHeader("Content-Security-Policy");
        $this->assertCount(1, $header);
        $this->assertStringContainsString(
            "connect-src 'self' https://api.bristolian.org",
            $header[0]
        );
    }
}