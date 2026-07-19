<?php

declare(strict_types=1);

namespace Bristolian\Service\ExplorerData;

use Bristolian\Cli\CliCommandRegistry;

class ControllersEntryTypeFinder implements EntryTypeFinder
{
    public function getEntryTypeKey(): string
    {
        return 'controllers';
    }

    public function findEntries(): array
    {
        /** @var array<string, list<string>> $methodsByClass */
        $methodsByClass = [];

        foreach ($this->collectControllerCallables() as $controllerCallable) {
            try {
                [$className, $methodName] = ControllerCallableCodeLocator::parseControllerCallable(
                    $controllerCallable
                );
            } catch (\InvalidArgumentException) {
                continue;
            }

            if (array_key_exists($className, $methodsByClass) === false) {
                $methodsByClass[$className] = [];
            }

            if (in_array($methodName, $methodsByClass[$className], true) === false) {
                $methodsByClass[$className][] = $methodName;
            }
        }

        $classNames = array_keys($methodsByClass);
        sort($classNames);

        $entries = [];

        foreach ($classNames as $className) {
            try {
                $entries[] = [
                    'name' => $className,
                    'dependencies' => $this->collectDependenciesForClass(
                        $className,
                        $methodsByClass[$className]
                    ),
                ];
            } catch (\ReflectionException) {
                continue;
            }
        }

        return $entries;
    }

    /**
     * @return list<string>
     */
    private function collectControllerCallables(): array
    {
        $controllerCallables = [];

        foreach (CliCommandRegistry::getAllDefinitions() as $definition) {
            $controllerCallables[] = $definition->controllerCallable;
        }

        $projectRoot = dirname(__DIR__, 4);

        require_once $projectRoot . '/app/src/app_routes.php';
        require_once $projectRoot . '/api/src/api_routes.php';

        foreach (array_merge(getAllAppRoutes(), getAllApiRoutes()) as $route) {
            if (is_array($route) === false || count($route) < 3) {
                continue;
            }

            $controllerCallable = $route[2];
            if (is_string($controllerCallable) === false || $controllerCallable === '') {
                continue;
            }

            $controllerCallables[] = $controllerCallable;
        }

        return $controllerCallables;
    }

    /**
     * @param list<string> $methodNames
     * @return list<string>
     */
    private function collectDependenciesForClass(string $className, array $methodNames): array
    {
        $reflectionClass = new \ReflectionClass($className);
        $dependencyNames = [];

        $constructor = $reflectionClass->getConstructor();
        if ($constructor !== null) {
            foreach ($this->classTypeNamesFromParameters($constructor->getParameters()) as $dependencyName) {
                $dependencyNames[$dependencyName] = true;
            }
        }

        foreach ($methodNames as $methodName) {
            if ($reflectionClass->hasMethod($methodName) === false) {
                continue;
            }

            $reflectionMethod = $reflectionClass->getMethod($methodName);
            foreach ($this->classTypeNamesFromParameters($reflectionMethod->getParameters()) as $dependencyName) {
                $dependencyNames[$dependencyName] = true;
            }
        }

        $dependencies = array_keys($dependencyNames);
        sort($dependencies);

        return $dependencies;
    }

    /**
     * @param list<\ReflectionParameter> $parameters
     * @return list<string>
     */
    private function classTypeNamesFromParameters(array $parameters): array
    {
        $typeNames = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if ($type instanceof \ReflectionNamedType) {
                if ($type->isBuiltin() === true) {
                    continue;
                }

                $typeNames[] = $type->getName();
                continue;
            }

            if ($type instanceof \ReflectionUnionType || $type instanceof \ReflectionIntersectionType) {
                foreach ($type->getTypes() as $innerType) {
                    if ($innerType instanceof \ReflectionNamedType && $innerType->isBuiltin() === false) {
                        $typeNames[] = $innerType->getName();
                    }
                }
            }
        }

        return $typeNames;
    }
}
