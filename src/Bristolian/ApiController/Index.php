<?php

declare(strict_types = 1);

namespace Bristolian\ApiController;

use SlimDispatcher\Response\JsonResponse;

class Index
{
    public function getRouteList(): JsonResponse
    {
        $routes = getAllApiRoutes();

        \Safe\error_log("sdfsdpfjpsdofj");

        return new JsonResponse($routes);
    }
}
