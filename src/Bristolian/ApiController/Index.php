<?php

declare(strict_types = 1);

namespace Bristolian\ApiController;

use SlimAuryn\Response\JsonResponse;

class Index
{
    public function getRouteList()
    {
        $routes = getAllRoutes();

        return new JsonResponse($routes);
    }
}
