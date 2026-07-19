<?php

declare(strict_types=1);

namespace Bristolian\Service\ExplorerData;

/**
 * Shared helpers for turning Slim route rows into codeview endpoint entries.
 */
class RouteEndpointEntryBuilder
{
    /**
     * App HTTP result mappers (mirrors getAppResultMappers in app/src/app_factories.php).
     *
     * @return array<string, string>
     */
    public static function getAppResultMappers(): array
    {
        return [
            \Bristolian\Response\StreamingResponse::class =>
                'mapStreamingResponseToPSR7',
            \SlimDispatcher\Response\StubResponse::class =>
                'SlimDispatcher\\mapStubResponseToPsr7',
            \Psr\Http\Message\ResponseInterface::class =>
                'SlimDispatcher\\passThroughResponse',
            'string' =>
                'Bristolian\\StringToHtmlPageConverter::convertStringToHtmlResponse',
        ];
    }

    /**
     * API result mappers (mirrors getApiResultMappers in api/src/api_factories.php).
     *
     * @return array<string, string>
     */
    public static function getApiResultMappers(): array
    {
        return [
            \SlimDispatcher\Response\StubResponse::class =>
                'SlimDispatcher\\mapStubResponseToPsr7',
            \Psr\Http\Message\ResponseInterface::class =>
                'SlimDispatcher\\passThroughResponse',
            'string' =>
                'convertStringToResponse',
        ];
    }

    /**
     * @param list<array<int, mixed>> $routes
     * @param array<string, string> $resultMappers
     * @return list<array<string, mixed>>
     */
    public static function buildEntriesFromRoutes(array $routes, array $resultMappers): array
    {
        $entries = [];

        foreach ($routes as $route) {
            if (count($route) < 3) {
                continue;
            }

            $path = $route[0];
            $method = $route[1];
            $controllerCallable = $route[2];

            if (is_string($path) === false || is_string($method) === false || is_string($controllerCallable) === false) {
                continue;
            }

            $returnTypes = self::resolveReturnTypes($controllerCallable);
            $responseMappers = [];

            foreach ($returnTypes as $returnType) {
                $mapper = self::resolveResultMapper($returnType, $resultMappers);
                if ($mapper !== null) {
                    $responseMappers[] = [
                        'return_type' => $returnType,
                        'mapper' => $mapper,
                    ];
                }
            }

            $entry = [
                'path' => $path,
                'method' => $method,
                'controller' => ControllerCallableCodeLocator::normalizeFqcn($controllerCallable),
                'return_types' => $returnTypes,
                'response_mappers' => $responseMappers,
            ];

            if (array_key_exists(4, $route) && is_string($route[4]) && $route[4] !== '') {
                $entry['request_body_param'] = ControllerCallableCodeLocator::normalizeFqcn($route[4]);
            }

            $entries[] = $entry;
        }

        return $entries;
    }

    /**
     * @return list<string>
     */
    public static function resolveReturnTypes(string $controllerCallable): array
    {
        try {
            [$className, $methodName] = ControllerCallableCodeLocator::parseControllerCallable(
                $controllerCallable
            );
            $reflectionMethod = new \ReflectionMethod($className, $methodName);
        } catch (\ReflectionException | \InvalidArgumentException) {
            return [];
        }

        $returnType = $reflectionMethod->getReturnType();
        if ($returnType === null) {
            return [];
        }

        return self::typeNamesFromReflectionType($returnType);
    }

    /**
     * @param array<string, string> $resultMappers
     */
    public static function resolveResultMapper(string $returnTypeName, array $resultMappers): ?string
    {
        $normalizedReturnType = ControllerCallableCodeLocator::normalizeFqcn($returnTypeName);

        foreach ($resultMappers as $mapperType => $mapper) {
            if ($mapperType === 'string') {
                if ($normalizedReturnType === 'string') {
                    return $mapper;
                }
                continue;
            }

            if (is_a($normalizedReturnType, $mapperType, true)) {
                return $mapper;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private static function typeNamesFromReflectionType(\ReflectionType $reflectionType): array
    {
        if ($reflectionType instanceof \ReflectionNamedType) {
            return [$reflectionType->getName()];
        }

        if ($reflectionType instanceof \ReflectionUnionType || $reflectionType instanceof \ReflectionIntersectionType) {
            $typeNames = [];
            foreach ($reflectionType->getTypes() as $innerType) {
                if ($innerType instanceof \ReflectionNamedType) {
                    $typeNames[] = $innerType->getName();
                }
            }

            return $typeNames;
        }

        return [];
    }
}
