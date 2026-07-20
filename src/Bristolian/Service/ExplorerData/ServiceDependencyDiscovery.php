<?php

declare(strict_types=1);

namespace Bristolian\Service\ExplorerData;

/**
 * Discovers Bristolian\Service types for the CodeView dependencies catalog.
 *
 * - Every service interface → entry with same-package implementations
 * - Injectable concrete contracts (not Fake*, not already an implementation of a
 *   listed service interface) → entry with same-package subclasses as implementations
 */
class ServiceDependencyDiscovery
{
    private const SERVICE_DIRECTORY = 'src/Bristolian/Service';

    private const SERVICE_NAMESPACE = 'Bristolian\\Service';

    /**
     * Meta generators under Service/ — not application dependency contracts.
     */
    private const SKIP_NAMESPACE_PREFIXES = [
        'Bristolian\\Service\\ExplorerData\\',
    ];

    /**
     * @return list<array{
     *     name: string,
     *     implementations: list<string>,
     *     methods: list<array{
     *         name: string,
     *         datasources: list<array{path: string, reads: bool, writes: bool}>
     *     }>
     * }>
     */
    public static function findEntries(string $projectRoot): array
    {
        $classNames = PhpClassUnderDirectoryDiscovery::filterLoadableTypes(
            PhpClassUnderDirectoryDiscovery::findClassNames(
                $projectRoot,
                self::SERVICE_DIRECTORY,
                self::SERVICE_NAMESPACE
            )
        );

        /** @var list<\ReflectionClass<object>> $interfaceReflections */
        $interfaceReflections = [];
        /** @var list<\ReflectionClass<object>> $concreteReflections */
        $concreteReflections = [];

        foreach ($classNames as $className) {
            if (self::shouldSkipNamespace($className) === true) {
                continue;
            }

            try {
                $reflectionClass = new \ReflectionClass($className);
            } catch (\ReflectionException) {
                continue;
            }

            if ($reflectionClass->isInterface() === true) {
                $interfaceReflections[] = $reflectionClass;
                continue;
            }

            if ($reflectionClass->isTrait() === true
                || $reflectionClass->isEnum() === true
                || $reflectionClass->isAbstract() === true
            ) {
                continue;
            }

            $shortName = $reflectionClass->getShortName();
            if (str_starts_with($shortName, 'Fake') === true
                || str_starts_with($shortName, 'InMemory') === true
            ) {
                continue;
            }

            $concreteReflections[] = $reflectionClass;
        }

        $entries = [];
        /** @var array<string, true> $listedContractNames */
        $listedContractNames = [];

        foreach ($interfaceReflections as $interfaceReflection) {
            $interfaceClassName = ControllerCallableCodeLocator::normalizeFqcn(
                $interfaceReflection->getName()
            );
            $listedContractNames[$interfaceClassName] = true;

            $entries[] = [
                'name' => $interfaceClassName,
                'implementations' => array_map(
                    static fn (string $implementationClassName): string =>
                        ControllerCallableCodeLocator::normalizeFqcn($implementationClassName),
                    RepoInterfaceImplementationDiscovery::findImplementationsForInterface(
                        $interfaceClassName,
                        $interfaceReflection
                    )
                ),
                'methods' => [],
            ];
        }

        foreach ($concreteReflections as $concreteReflection) {
            $concreteClassName = ControllerCallableCodeLocator::normalizeFqcn(
                $concreteReflection->getName()
            );

            if (self::implementsListedServiceInterface($concreteReflection, $listedContractNames) === true) {
                continue;
            }

            if (self::looksLikeValueOrErrorType($concreteReflection) === true) {
                continue;
            }

            $listedContractNames[$concreteClassName] = true;

            $entries[] = [
                'name' => $concreteClassName,
                'implementations' => self::findSubclassesInPackage($concreteReflection),
                'methods' => [],
            ];
        }

        usort(
            $entries,
            static fn (array $left, array $right): int => strcmp($left['name'], $right['name'])
        );

        return $entries;
    }

    private static function shouldSkipNamespace(string $className): bool
    {
        foreach (self::SKIP_NAMESPACE_PREFIXES as $prefix) {
            if (str_starts_with($className, $prefix) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \ReflectionClass<object> $concreteReflection
     * @param array<string, true> $listedContractNames
     */
    private static function implementsListedServiceInterface(
        \ReflectionClass $concreteReflection,
        array $listedContractNames
    ): bool {
        foreach ($concreteReflection->getInterfaces() as $interfaceReflection) {
            $interfaceName = ControllerCallableCodeLocator::normalizeFqcn(
                $interfaceReflection->getName()
            );
            if (array_key_exists($interfaceName, $listedContractNames) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     */
    private static function looksLikeValueOrErrorType(\ReflectionClass $reflectionClass): bool
    {
        $shortName = $reflectionClass->getShortName();

        if ($reflectionClass->isSubclassOf(\Throwable::class) === true) {
            return true;
        }

        foreach (['Error', 'Exception', 'Result', 'Info', 'Report'] as $suffix) {
            if (str_ends_with($shortName, $suffix) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \ReflectionClass<object> $baseReflection
     * @return list<string>
     */
    private static function findSubclassesInPackage(\ReflectionClass $baseReflection): array
    {
        $baseFileName = $baseReflection->getFileName();
        if ($baseFileName === false) {
            return [];
        }

        $packageDirectory = dirname($baseFileName);
        $subclassNames = [];

        foreach (glob($packageDirectory . '/*.php') ?: [] as $phpFilePath) {
            $shortName = basename($phpFilePath, '.php');
            if ($shortName === $baseReflection->getShortName()) {
                continue;
            }

            $fileContents = file_get_contents($phpFilePath);
            if ($fileContents === false) {
                continue;
            }

            if (preg_match(
                '/\bclass\s+' . preg_quote($shortName, '/') . '\b/',
                $fileContents
            ) !== 1) {
                continue;
            }

            $candidateClassName = $baseReflection->getNamespaceName() . '\\' . $shortName;

            try {
                $candidateReflection = new \ReflectionClass($candidateClassName);
            } catch (\ReflectionException) {
                continue;
            }

            if ($candidateReflection->isInterface() || $candidateReflection->isAbstract()) {
                continue;
            }

            if ($candidateReflection->isSubclassOf($baseReflection->getName()) === false) {
                continue;
            }

            $subclassNames[] = ControllerCallableCodeLocator::normalizeFqcn($candidateClassName);
        }

        sort($subclassNames);

        return $subclassNames;
    }
}
