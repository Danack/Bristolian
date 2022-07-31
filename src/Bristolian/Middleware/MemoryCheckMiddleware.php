<?php

declare(strict_types = 1);

namespace Bristolian\Middleware;

//use Psr\Http\Message\ResponseInterface;
use Bristolian\Service\MemoryWarningCheck\MemoryWarningCheck;
//use Psr\Http\Message\ServerRequestInterface as Request;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;


// Check to make sure that not too much memory is being used.
// in dev, just return an error.
// in production, log it.

class MemoryCheckMiddleware
{
    /** @var \Bristolian\Service\MemoryWarningCheck\MemoryWarningCheck */
    private $memoryWarningCheck;

    /**
     *
     * @param MemoryWarningCheck $memoryWarningCheck
     */
    public function __construct(MemoryWarningCheck $memoryWarningCheck)
    {
        $this->memoryWarningCheck = $memoryWarningCheck;
    }

    /**
     * @param Request $request
     * @param RequestHandler $handler
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);
        $percentMemoryUsed = $this->memoryWarningCheck->checkMemoryUsage($request);

        $response = $response->withAddedHeader('X-Debug-Memory', $percentMemoryUsed . '%');

        return $response;
    }
}
