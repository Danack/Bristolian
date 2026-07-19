<?php

declare(strict_types=1);

namespace Bristolian\Service\ExplorerData;

class HttpEndpointsEntryTypeFinder implements EntryTypeFinder
{
    public function getEntryTypeKey(): string
    {
        return 'http_endpoints';
    }

    public function findEntries(): array
    {
        $routesFile = dirname(__DIR__, 4) . '/app/src/app_routes.php';
        require_once $routesFile;

        /** @var list<array<int, mixed>> $routes */
        $routes = getAllAppRoutes();

        return RouteEndpointEntryBuilder::buildEntriesFromRoutes(
            $routes,
            RouteEndpointEntryBuilder::getAppResultMappers()
        );
    }
}
