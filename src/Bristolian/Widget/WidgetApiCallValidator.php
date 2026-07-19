<?php

declare(strict_types=1);

namespace Bristolian\Widget;

use Bristolian\Exception\BristolianException;

/**
 * Ensures widget api_calls reference real api_routes entries (METHOD + path keys).
 */
final class WidgetApiCallValidator
{
    /**
     * @param list<WidgetDefinition> $definitions
     * @param list<array<int, mixed>> $apiRoutes
     */
    public static function validateDefinitionsAgainstApiRoutes(
        array $definitions,
        array $apiRoutes
    ): void {
        $knownRouteKeys = self::buildKnownRouteKeys($apiRoutes);

        foreach ($definitions as $definition) {
            $seenWithinWidget = [];

            foreach ($definition->apiCalls as $apiCall) {
                $routeKey = $apiCall->routeKey();

                if (array_key_exists($routeKey, $seenWithinWidget) === true) {
                    throw new BristolianException(
                        "WidgetRegistry: duplicate api_call for widget {$definition->cssClass}: {$routeKey}"
                    );
                }
                $seenWithinWidget[$routeKey] = true;

                if (array_key_exists($routeKey, $knownRouteKeys) === false) {
                    throw new BristolianException(
                        "WidgetRegistry: api_call not found in api_routes for widget {$definition->cssClass}: {$routeKey}"
                    );
                }
            }
        }
    }

    /**
     * @param list<array<int, mixed>> $apiRoutes
     * @return array<string, true>
     */
    public static function buildKnownRouteKeys(array $apiRoutes): array
    {
        $knownRouteKeys = [];

        foreach ($apiRoutes as $route) {
            if (count($route) < 2) {
                continue;
            }

            $path = $route[0];
            $method = $route[1];
            if (is_string($path) === false || is_string($method) === false) {
                continue;
            }

            $knownRouteKeys[$method . ' ' . $path] = true;
        }

        return $knownRouteKeys;
    }
}
