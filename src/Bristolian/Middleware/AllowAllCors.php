<?php

declare(strict_types = 1);

namespace Bristolian\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Class AllowAllCors
 */
class AllowAllCors
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);
        $response = $response->withHeader('Access-Control-Allow-Origin', '*');
        $response = $response->withHeader('Access-Control-Allow-Methods', 'GET,POST,DELETE,PUT,OPTIONS,HEAD,PATCH');
        $response = $response->withHeader('Access-Control-Allow-Headers', '*');

        return $response;
    }
}
