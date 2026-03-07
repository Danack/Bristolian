<?php

declare(strict_types=1);

namespace Bristolian\Middleware;

use Bristolian\Cache\TableAccessRecorder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class CacheTagMiddleware
{
    public function __construct(
        private TableAccessRecorder $recorder
    ) {
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);

        $method = $request->getMethod();
        $statusCode = $response->getStatusCode();

        if (($method === 'GET' || $method === 'HEAD') && $statusCode === 200) {
            $tags = $this->recorder->getTagsForResponse();
            if ($tags !== '') {
                $response = $response->withHeader('X-Cache-Tags', $tags);
            }
        }

        $tablesWritten = $this->recorder->getTablesWritten();
        foreach ($tablesWritten as $table) {
            banVarnishByTag($table);
        }

        return $response;
    }
}
