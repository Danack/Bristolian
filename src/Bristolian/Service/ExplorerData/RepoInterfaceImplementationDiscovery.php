<?php

declare(strict_types=1);

namespace Bristolian\Service\ExplorerData;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use SplFileInfo;

class RepoInterfaceImplementationDiscovery
{
    private const REPO_DIRECTORY = 'src/Bristolian/Repo';

    public static function datasourcePathForTableName(string $tableName): string
    {
        return '/datasources/' . $tableName;
    }

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
    public static function findAnnotatedInterfaceEntries(string $projectRoot): array
    {
        $repoDirectoryPath = $projectRoot . '/' . self::REPO_DIRECTORY;

        if (is_dir($repoDirectoryPath) === false) {
            throw new \RuntimeException(
                'Repo directory not found: ' . $repoDirectoryPath
            );
        }

        $entries = [];

        foreach (self::findRepoInterfaceClassNames($repoDirectoryPath) as $interfaceClassName) {
            try {
                $reflectionClass = new \ReflectionClass($interfaceClassName);
            } catch (\ReflectionException) {
                continue;
            }

            if ($reflectionClass->isInterface() === false) {
                continue;
            }

            $methodEntries = self::collectMethodDatasourceEntries($reflectionClass);
            if ($methodEntries === []) {
                continue;
            }

            $entries[] = [
                'name' => ControllerCallableCodeLocator::normalizeFqcn($interfaceClassName),
                'implementations' => array_map(
                    static fn (string $implementationClassName): string =>
                        ControllerCallableCodeLocator::normalizeFqcn($implementationClassName),
                    self::findImplementationsForInterface(
                        $interfaceClassName,
                        $reflectionClass
                    )
                ),
                'methods' => $methodEntries,
            ];
        }

        usort(
            $entries,
            static fn (array $left, array $right): int => strcmp($left['name'], $right['name'])
        );

        return $entries;
    }

    /**
     * @return list<array{name: string, type: string}>
     */
    public static function findDatasourceEntries(string $projectRoot): array
    {
        $repoDirectoryPath = $projectRoot . '/' . self::REPO_DIRECTORY;

        if (is_dir($repoDirectoryPath) === false) {
            throw new \RuntimeException(
                'Repo directory not found: ' . $repoDirectoryPath
            );
        }

        /** @var array<string, array{name: string, type: string}> $datasourcesByName */
        $datasourcesByName = [];

        foreach (self::findRepoInterfaceClassNames($repoDirectoryPath) as $interfaceClassName) {
            try {
                $reflectionClass = new \ReflectionClass($interfaceClassName);
            } catch (\ReflectionException) {
                continue;
            }

            if ($reflectionClass->isInterface() === false) {
                continue;
            }

            foreach (self::aggregateDatasourceUsages(
                self::collectMethodDatasourceEntries($reflectionClass)
            ) as $datasourceUsage) {
                $tableName = $datasourceUsage['name'];
                if (array_key_exists($tableName, $datasourcesByName)) {
                    continue;
                }

                $datasourcesByName[$tableName] = [
                    'name' => $tableName,
                    'type' => 'database',
                ];
            }
        }

        $datasources = array_values($datasourcesByName);
        usort(
            $datasources,
            static fn (array $left, array $right): int => strcmp($left['name'], $right['name'])
        );

        return $datasources;
    }

    /**
     * @param \ReflectionClass<object>|null $interfaceReflection
     * @return list<string>
     */
    public static function findImplementationsForInterface(
        string $interfaceClassName,
        ?\ReflectionClass $interfaceReflection = null
    ): array {
        if ($interfaceReflection === null) {
            $interfaceReflection = new \ReflectionClass($interfaceClassName);
        }

        $interfaceFileName = $interfaceReflection->getFileName();
        if ($interfaceFileName === false) {
            return [];
        }

        $packageDirectory = dirname($interfaceFileName);
        $implementationClassNames = [];

        foreach (glob($packageDirectory . '/*.php') ?: [] as $phpFilePath) {
            $shortName = basename($phpFilePath, '.php');
            if ($shortName === $interfaceReflection->getShortName()) {
                continue;
            }

            $fileContents = file_get_contents($phpFilePath);
            if ($fileContents === false) {
                continue;
            }

            if (preg_match(
                '/\b(?:class|interface|enum)\s+' . preg_quote($shortName, '/') . '\b/',
                $fileContents
            ) !== 1) {
                continue;
            }

            $candidateClassName = $interfaceReflection->getNamespaceName() . '\\' . $shortName;

            try {
                $candidateReflection = new \ReflectionClass($candidateClassName);
            } catch (\ReflectionException) {
                continue;
            }

            if ($candidateReflection->isInterface() || $candidateReflection->isAbstract()) {
                continue;
            }

            if ($candidateReflection->implementsInterface($interfaceClassName) === false) {
                continue;
            }

            $implementationClassNames[] = $candidateClassName;
        }

        sort($implementationClassNames);

        return $implementationClassNames;
    }

    /**
     * @return list<string>
     */
    private static function findRepoInterfaceClassNames(string $repoDirectoryPath): array
    {
        $directoryIterator = new RecursiveDirectoryIterator(
            $repoDirectoryPath,
            RecursiveDirectoryIterator::SKIP_DOTS
        );
        $fileIterator = new RecursiveIteratorIterator($directoryIterator);
        $phpFiles = new RegexIterator($fileIterator, '/^.+Repo\.php$/i', RegexIterator::GET_MATCH);

        $classNames = [];

        /** @var array{0: string} $match */
        foreach ($phpFiles as $match) {
            $filePath = $match[0];
            $fileInfo = new SplFileInfo($filePath);
            $shortName = $fileInfo->getBasename('.php');

            if (str_starts_with($shortName, 'Fake') || str_starts_with($shortName, 'InMemory')) {
                continue;
            }

            if (str_starts_with($shortName, 'Pdo')) {
                continue;
            }

            $relativePath = substr($filePath, strlen($repoDirectoryPath) + 1);
            $relativePathWithoutExtension = substr($relativePath, 0, -strlen('.php'));
            $classNames[] = 'Bristolian\\Repo\\' . str_replace('/', '\\', $relativePathWithoutExtension);
        }

        sort($classNames);

        return $classNames;
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     * @return list<array{
     *     name: string,
     *     datasources: list<array{path: string, reads: bool, writes: bool}>
     * }>
     */
    private static function collectMethodDatasourceEntries(\ReflectionClass $reflectionClass): array
    {
        $methodEntries = [];

        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $datasourcesByTableHelper = [];

            foreach ($reflectionMethod->getAttributes(ReadsTable::class) as $attribute) {
                /** @var ReadsTable $readsTable */
                $readsTable = $attribute->newInstance();
                $tableHelperClass = $readsTable->tableHelperClass;
                $tableName = self::tableNameFromHelperClass($tableHelperClass);

                if (array_key_exists($tableHelperClass, $datasourcesByTableHelper) === false) {
                    $datasourcesByTableHelper[$tableHelperClass] = [
                        'name' => $tableName,
                        'path' => self::datasourcePathForTableName($tableName),
                        'reads' => false,
                        'writes' => false,
                    ];
                }

                $datasourcesByTableHelper[$tableHelperClass]['reads'] = true;
            }

            foreach ($reflectionMethod->getAttributes(WritesTable::class) as $attribute) {
                /** @var WritesTable $writesTable */
                $writesTable = $attribute->newInstance();
                $tableHelperClass = $writesTable->tableHelperClass;
                $tableName = self::tableNameFromHelperClass($tableHelperClass);

                if (array_key_exists($tableHelperClass, $datasourcesByTableHelper) === false) {
                    $datasourcesByTableHelper[$tableHelperClass] = [
                        'name' => $tableName,
                        'path' => self::datasourcePathForTableName($tableName),
                        'reads' => false,
                        'writes' => false,
                    ];
                }

                $datasourcesByTableHelper[$tableHelperClass]['writes'] = true;
            }

            if ($datasourcesByTableHelper === []) {
                continue;
            }

            $datasources = array_values($datasourcesByTableHelper);
            usort(
                $datasources,
                static fn (array $left, array $right): int => strcmp($left['path'], $right['path'])
            );

            $methodEntries[] = [
                'name' => $reflectionMethod->getName(),
                'datasources' => array_map(
                    static fn (array $datasource): array => [
                        'path' => $datasource['path'],
                        'reads' => $datasource['reads'],
                        'writes' => $datasource['writes'],
                    ],
                    $datasources
                ),
            ];
        }

        usort(
            $methodEntries,
            static fn (array $left, array $right): int => strcmp($left['name'], $right['name'])
        );

        return $methodEntries;
    }

    /**
     * @param list<array{
     *     name: string,
     *     datasources: list<array{path: string, reads: bool, writes: bool}>
     * }> $methodEntries
     * @return list<array{name: string, path: string, reads: bool, writes: bool}>
     */
    private static function aggregateDatasourceUsages(array $methodEntries): array
    {
        /** @var array<string, array{name: string, path: string, reads: bool, writes: bool}> $datasourcesByPath */
        $datasourcesByPath = [];

        foreach ($methodEntries as $methodEntry) {
            foreach ($methodEntry['datasources'] as $datasource) {
                $path = $datasource['path'];
                $tableName = substr($path, strlen('/datasources/'));

                if (array_key_exists($path, $datasourcesByPath) === false) {
                    $datasourcesByPath[$path] = [
                        'name' => $tableName,
                        'path' => $path,
                        'reads' => false,
                        'writes' => false,
                    ];
                }

                if ($datasource['reads']) {
                    $datasourcesByPath[$path]['reads'] = true;
                }
                if ($datasource['writes']) {
                    $datasourcesByPath[$path]['writes'] = true;
                }
            }
        }

        $datasources = array_values($datasourcesByPath);
        usort(
            $datasources,
            static fn (array $left, array $right): int => strcmp($left['path'], $right['path'])
        );

        return $datasources;
    }

    private static function tableNameFromHelperClass(string $tableHelperClass): string
    {
        $separatorPosition = strrpos($tableHelperClass, '\\');
        if ($separatorPosition === false) {
            return $tableHelperClass;
        }

        return substr($tableHelperClass, $separatorPosition + 1);
    }
}
