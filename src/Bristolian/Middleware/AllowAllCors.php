<?php

declare(strict_types = 1);

namespace Bristolian\Middleware;

use Laminas\Diactoros\Response as ConcreteResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Class AllowAllCors
 */
class AllowAllCors implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        // Handle OPTIONS preflight requests
        if ($request->getMethod() === 'OPTIONS') {
            $response = new ConcreteResponse();
            $response = $response->withHeader('Access-Control-Allow-Origin', '*');
            $response = $response->withHeader('Access-Control-Allow-Methods', 'GET,POST,DELETE,PUT,OPTIONS,HEAD,PATCH');
            $response = $response->withHeader('Access-Control-Allow-Headers', '*');
            $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
            $response = $response->withHeader('Access-Control-Max-Age', '86400');
            return $response->withStatus(200);
        }

        $response = $handler->handle($request);
        $response = $response->withHeader('Access-Control-Allow-Origin', '*');
        $response = $response->withHeader('Access-Control-Allow-Methods', 'GET,POST,DELETE,PUT,OPTIONS,HEAD,PATCH');
        $response = $response->withHeader('Access-Control-Allow-Headers', '*');
        $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}
