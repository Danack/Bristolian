<?php

namespace BristolianTest\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response;

class FakeRequestHandler implements RequestHandlerInterface
{
    public function __construct(
        private int $statusCode = 200
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return (new Response())->withStatus($this->statusCode);
    }
}

/**
 * RequestHandler that throws a given throwable when handle() is called.
 */
class ThrowingRequestHandler implements RequestHandlerInterface
{
    public function __construct(
        private \Throwable $throwable
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        throw $this->throwable;
    }
}
