<?php

declare(strict_types=1);

namespace Bristolian\PHPStan;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatExtendClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use PHPUnit\Framework\Attributes\CoversDirectory;
use PHPUnit\Framework\Attributes\CoversDirectoryRecursively;
use PHPUnit\Framework\Attributes\CoversFile;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\CoversTrait;
use ReflectionClass;

use function Safe\preg_match;

/**
 * Detects PHPUnit class-level coverage attributes on test classes.
 */
class TestClassCoversAttributeDetector
{
    /**
     * @var list<class-string>
     */
    public const COVER_ATTRIBUTE_CLASSES = [
        CoversNothing::class,
        CoversClass::class,
        CoversMethod::class,
        CoversFunction::class,
        CoversTrait::class,
        CoversNamespace::class,
        CoversDirectory::class,
        CoversDirectoryRecursively::class,
        CoversFile::class,
        CoversClassesThatExtendClass::class,
        CoversClassesThatImplementInterface::class,
    ];

    /**
     * @param list<string> $attributeClassNames Fully-qualified attribute class names
     */
    public function hasCoversAttribute(array $attributeClassNames): bool
    {
        foreach ($attributeClassNames as $attributeClassName) {
            if (in_array($attributeClassName, self::COVER_ATTRIBUTE_CLASSES, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     */
    public function reflectionClassHasCoversAttribute(ReflectionClass $reflectionClass): bool
    {
        $attributeClassNames = [];
        foreach ($reflectionClass->getAttributes() as $attribute) {
            $attributeClassNames[] = $attribute->getName();
        }

        return $this->hasCoversAttribute($attributeClassNames);
    }

    public function isTestClassShortName(string $shortName): bool
    {
        return str_ends_with($shortName, 'Test');
    }

    public function isPathUnderTestDirectory(string $absoluteOrRelativePath): bool
    {
        $normalizedPath = str_replace('\\', '/', $absoluteOrRelativePath);

        if (preg_match('#(?:^|/)test/#', $normalizedPath) === 1) {
            return true;
        }

        return str_starts_with($normalizedPath, 'test/') || $normalizedPath === 'test';
    }
}
