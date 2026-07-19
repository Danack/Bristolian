<?php

declare(strict_types=1);

namespace Bristolian\Service\ExplorerData;

use Bristolian\Cli\CliCommandRegistry;

class CodeMapEntryTypeFinder implements EntryTypeFinder
{
    public function getEntryTypeKey(): string
    {
        return 'code-map';
    }

    public function findEntries(): array
    {
        $projectRoot = dirname(__DIR__, 4);
        $entriesByName = [];

        foreach ($this->collectControllerCallables($projectRoot) as $controllerCallable) {
            $normalizedCallable = ControllerCallableCodeLocator::normalizeFqcn($controllerCallable);

            try {
                $entriesByName[$normalizedCallable] = ControllerCallableCodeLocator::locate(
                    $normalizedCallable,
                    $projectRoot
                );
            } catch (\ReflectionException) {
                continue;
            }
        }

        foreach (RepoInterfaceImplementationDiscovery::findAnnotatedInterfaceEntries($projectRoot) as $dependencyEntry) {
            $classNames = array_merge(
                [$dependencyEntry['name']],
                $dependencyEntry['implementations']
            );

            foreach ($classNames as $className) {
                $normalizedClassName = ControllerCallableCodeLocator::normalizeFqcn($className);
                if (array_key_exists($normalizedClassName, $entriesByName)) {
                    continue;
                }

                try {
                    $entriesByName[$normalizedClassName] = ControllerCallableCodeLocator::locateClass(
                        $normalizedClassName,
                        $projectRoot
                    );
                } catch (\ReflectionException) {
                    continue;
                }
            }
        }

        foreach (self::collectServiceClassNames($projectRoot) as $serviceClassName) {
            if (array_key_exists($serviceClassName, $entriesByName)) {
                continue;
            }

            try {
                $entriesByName[$serviceClassName] = ControllerCallableCodeLocator::locateClass(
                    $serviceClassName,
                    $projectRoot
                );
            } catch (\ReflectionException) {
                continue;
            }
        }

        $entries = array_values($entriesByName);
        usort(
            $entries,
            static fn (array $left, array $right): int => strcmp($left['name'], $right['name'])
        );

        return $entries;
    }

    /**
     * @return list<string>
     */
    private function collectControllerCallables(string $projectRoot): array
    {
        $controllerCallables = [];

        foreach (CliCommandRegistry::getAllDefinitions() as $definition) {
            $controllerCallables[] = $definition->controllerCallable;
        }

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
     * @return list<string>
     */
    private static function collectServiceClassNames(string $projectRoot): array
    {
        $classNames = PhpClassUnderDirectoryDiscovery::findClassNames(
            $projectRoot,
            'src/Bristolian/Service',
            'Bristolian\\Service'
        );

        return PhpClassUnderDirectoryDiscovery::filterLoadableTypes($classNames);
    }
}
