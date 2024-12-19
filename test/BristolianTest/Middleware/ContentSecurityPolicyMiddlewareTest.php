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
 * @coversNothing
 */
class ContentSecurityPolicyMiddlewareTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Middleware\ContentSecurityPolicyMiddleware
     */
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
        // TODO - needs more assertions.
    }
}