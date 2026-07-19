<?php

declare(strict_types=1);

namespace Bristolian\Service\ExplorerData;

class ControllerCallableCodeLocator
{
    /**
     * Normalize Class::method or FQCN strings used as join keys across codeview JSON.
     */
    public static function normalizeFqcn(string $name): string
    {
        return ltrim($name, '\\');
    }

    /**
     * @return array{name: string, file: string, line-start: int, line-end: int, dependencies: list<string>}
     */
    public static function locate(string $controllerCallable, string $projectRoot): array
    {
        [$className, $methodName] = self::parseControllerCallable($controllerCallable);

        $reflectionMethod = new \ReflectionMethod($className, $methodName);
        $absolutePath = $reflectionMethod->getFileName();

        if ($absolutePath === false) {
            throw new \RuntimeException(
                'Could not resolve file for controller callable: ' . $controllerCallable
            );
        }

        return [
            'name' => self::normalizeFqcn($controllerCallable),
            'file' => self::pathRelativeToProjectRoot($absolutePath, $projectRoot),
            'line-start' => $reflectionMethod->getStartLine(),
            'line-end' => $reflectionMethod->getEndLine(),
            'dependencies' => self::collectDependenciesForCallable($className, $methodName),
        ];
    }

    /**
     * @return array{name: string, file: string, line-start: int, line-end: int, dependencies: list<string>}
     */
    public static function locateClass(string $className, string $projectRoot): array
    {
        $normalizedClassName = self::normalizeFqcn($className);
        $reflectionClass = new \ReflectionClass($normalizedClassName);
        $absolutePath = $reflectionClass->getFileName();

        if ($absolutePath === false) {
            throw new \RuntimeException(
                'Could not resolve file for class: ' . $className
            );
        }

        return [
            'name' => $normalizedClassName,
            'file' => self::pathRelativeToProjectRoot($absolutePath, $projectRoot),
            'line-start' => $reflectionClass->getStartLine(),
            'line-end' => $reflectionClass->getEndLine(),
            'dependencies' => [],
        ];
    }

    /**
     * @return list<string>
     */
    public static function collectDependenciesForCallable(string $className, string $methodName): array
    {
        $reflectionClass = new \ReflectionClass($className);
        $dependencyNames = [];

        $constructor = $reflectionClass->getConstructor();
        if ($constructor !== null) {
            foreach (self::classTypeNamesFromParameters($constructor->getParameters()) as $dependencyName) {
                $dependencyNames[$dependencyName] = true;
            }
        }

        if ($reflectionClass->hasMethod($methodName)) {
            $reflectionMethod = $reflectionClass->getMethod($methodName);
            foreach (self::classTypeNamesFromParameters($reflectionMethod->getParameters()) as $dependencyName) {
                $dependencyNames[$dependencyName] = true;
            }
        }

        $dependencies = array_keys($dependencyNames);
        sort($dependencies);

        return $dependencies;
    }

    /**
     * @return array{0: string, 1: string}
     */
    public static function parseControllerCallable(string $controllerCallable): array
    {
        $normalizedCallable = self::normalizeFqcn($controllerCallable);
        $separatorPosition = strrpos($normalizedCallable, '::');

        if ($separatorPosition === false) {
            throw new \InvalidArgumentException(
                'Controller callable must be in Class::method form: ' . $controllerCallable
            );
        }

        $className = substr($normalizedCallable, 0, $separatorPosition);
        $methodName = substr($normalizedCallable, $separatorPosition + 2);

        if ($className === '' || $methodName === '') {
            throw new \InvalidArgumentException(
                'Controller callable must be in Class::method form: ' . $controllerCallable
            );
        }

        return [$className, $methodName];
    }

    /**
     * @param list<\ReflectionParameter> $parameters
     * @return list<string>
     */
    private static function classTypeNamesFromParameters(array $parameters): array
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

    private static function pathRelativeToProjectRoot(string $absolutePath, string $projectRoot): string
    {
        $projectRootWithSeparator = rtrim($projectRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (str_starts_with($absolutePath, $projectRootWithSeparator)) {
            return substr($absolutePath, strlen($projectRootWithSeparator));
        }

        return $absolutePath;
    }
}
