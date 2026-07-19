<?php

declare(strict_types=1);

namespace Bristolian\Service\ExplorerData;

class ApiEndpointsEntryTypeFinder implements EntryTypeFinder
{
    public function getEntryTypeKey(): string
    {
        return 'api_endpoints';
    }

    public function findEntries(): array
    {
        $routesFile = dirname(__DIR__, 4) . '/api/src/api_routes.php';
        require_once $routesFile;

        /** @var list<array<int, mixed>> $routes */
        $routes = getAllApiRoutes();

        return RouteEndpointEntryBuilder::buildEntriesFromRoutes(
            $routes,
            RouteEndpointEntryBuilder::getApiResultMappers()
        );
    }
}
