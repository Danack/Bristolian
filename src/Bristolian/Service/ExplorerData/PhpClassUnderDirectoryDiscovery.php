<?php

declare(strict_types=1);

namespace Bristolian\Service\ExplorerData;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

/**
 * Discover PHP FQCNs under a project subdirectory by relative path → namespace mapping.
 */
class PhpClassUnderDirectoryDiscovery
{
    /**
     * @return list<string>
     */
    public static function findClassNames(
        string $projectRoot,
        string $relativeDirectory,
        string $namespacePrefix
    ): array {
        $directoryPath = $projectRoot . '/' . $relativeDirectory;

        if (is_dir($directoryPath) === false) {
            throw new \RuntimeException('Directory not found: ' . $directoryPath);
        }

        $directoryIterator = new RecursiveDirectoryIterator(
            $directoryPath,
            RecursiveDirectoryIterator::SKIP_DOTS
        );
        $fileIterator = new RecursiveIteratorIterator($directoryIterator);
        $phpFiles = new RegexIterator($fileIterator, '/^.+\.php$/i', RegexIterator::GET_MATCH);

        $classNames = [];

        /** @var array{0: string} $match */
        foreach ($phpFiles as $match) {
            $filePath = $match[0];
            $relativePath = substr($filePath, strlen($directoryPath) + 1);
            $relativePathWithoutExtension = substr($relativePath, 0, -strlen('.php'));
            $classNames[] = $namespacePrefix . '\\' . str_replace('/', '\\', $relativePathWithoutExtension);
        }

        sort($classNames);

        return $classNames;
    }

    /**
     * Keep types that Reflection can load (classes, interfaces, enums, traits).
     *
     * @param list<string> $classNames
     * @return list<string>
     */
    public static function filterLoadableTypes(array $classNames): array
    {
        $loadable = [];

        foreach ($classNames as $className) {
            $normalizedClassName = ControllerCallableCodeLocator::normalizeFqcn($className);

            try {
                new \ReflectionClass($normalizedClassName);
            } catch (\ReflectionException) {
                continue;
            }

            $loadable[] = $normalizedClassName;
        }

        return $loadable;
    }
}
