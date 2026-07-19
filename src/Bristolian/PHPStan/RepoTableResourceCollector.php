<?php

declare(strict_types=1);

namespace Bristolian\PHPStan;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Scalar\EncapsedStringPart;
use PhpParser\Node\Scalar\InterpolatedString;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeFinder;
use ReflectionMethod;

/**
 * Collects and compares method-level ReadsTable/WritesTable attributes against
 * Database::* constant usage and SQL string table references.
 */
class RepoTableResourceCollector
{
    public const DATABASE_NAMESPACE_PREFIX = 'Bristolian\\Database\\';

    private const READ_CONSTANTS = ['SELECT'];

    private const WRITE_CONSTANTS = ['INSERT', 'UPDATE'];

    private SqlTableReferenceExtractor $sqlTableReferenceExtractor;

    public function __construct(?SqlTableReferenceExtractor $sqlTableReferenceExtractor = null)
    {
        $this->sqlTableReferenceExtractor = $sqlTableReferenceExtractor ?? new SqlTableReferenceExtractor();
    }

    /**
     * @param list<string> $declaredReads Fully-qualified table helper class names
     * @param list<string> $declaredWrites Fully-qualified table helper class names
     * @param list<string> $usedReads Fully-qualified table helper class names
     * @param list<string> $usedWrites Fully-qualified table helper class names
     * @return list<string>
     */
    public function diff(
        array $declaredReads,
        array $declaredWrites,
        array $usedReads,
        array $usedWrites,
        string $methodLabel = ''
    ): array {
        $declaredReads = $this->normalizeClassList($declaredReads);
        $declaredWrites = $this->normalizeClassList($declaredWrites);
        $usedReads = $this->normalizeClassList($usedReads);
        $usedWrites = $this->normalizeClassList($usedWrites);

        if ($declaredReads === [] && $declaredWrites === [] && $usedReads === [] && $usedWrites === []) {
            return [];
        }

        $prefix = $methodLabel !== '' ? $methodLabel . ': ' : '';
        $messages = [];

        foreach ($usedReads as $tableHelperClass) {
            if (!in_array($tableHelperClass, $declaredReads, true)) {
                $messages[] = sprintf(
                    '%suses %s (read) but is missing #[ReadsTable(%s::class)].',
                    $prefix,
                    $tableHelperClass,
                    $tableHelperClass
                );
            }
        }

        foreach ($usedWrites as $tableHelperClass) {
            if (!in_array($tableHelperClass, $declaredWrites, true)) {
                $messages[] = sprintf(
                    '%suses %s (write) but is missing #[WritesTable(%s::class)].',
                    $prefix,
                    $tableHelperClass,
                    $tableHelperClass
                );
            }
        }

        foreach ($declaredReads as $tableHelperClass) {
            if (!in_array($tableHelperClass, $usedReads, true)) {
                $messages[] = sprintf(
                    '%sdeclares #[ReadsTable(%s::class)] but does not read that table.',
                    $prefix,
                    $tableHelperClass
                );
            }
        }

        foreach ($declaredWrites as $tableHelperClass) {
            if (!in_array($tableHelperClass, $usedWrites, true)) {
                $messages[] = sprintf(
                    '%sdeclares #[WritesTable(%s::class)] but does not write that table.',
                    $prefix,
                    $tableHelperClass
                );
            }
        }

        return $messages;
    }

    /**
     * Implementation methods must repeat the interface method attributes (emphasis).
     *
     * @param list<string> $interfaceReads
     * @param list<string> $interfaceWrites
     * @param list<string> $implementationReads
     * @param list<string> $implementationWrites
     * @return list<string>
     */
    public function diffImplementationAttributesAgainstInterface(
        array $interfaceReads,
        array $interfaceWrites,
        array $implementationReads,
        array $implementationWrites,
        string $interfaceMethodLabel,
        string $implementationMethodLabel
    ): array {
        $interfaceReads = $this->normalizeClassList($interfaceReads);
        $interfaceWrites = $this->normalizeClassList($interfaceWrites);
        $implementationReads = $this->normalizeClassList($implementationReads);
        $implementationWrites = $this->normalizeClassList($implementationWrites);

        $messages = [];

        foreach ($interfaceReads as $tableHelperClass) {
            if (!in_array($tableHelperClass, $implementationReads, true)) {
                $messages[] = sprintf(
                    '%s declares #[ReadsTable(%s::class)] but %s is missing that attribute (repeat on the implementation method).',
                    $interfaceMethodLabel,
                    $tableHelperClass,
                    $implementationMethodLabel
                );
            }
        }

        foreach ($interfaceWrites as $tableHelperClass) {
            if (!in_array($tableHelperClass, $implementationWrites, true)) {
                $messages[] = sprintf(
                    '%s declares #[WritesTable(%s::class)] but %s is missing that attribute (repeat on the implementation method).',
                    $interfaceMethodLabel,
                    $tableHelperClass,
                    $implementationMethodLabel
                );
            }
        }

        foreach ($implementationReads as $tableHelperClass) {
            if (!in_array($tableHelperClass, $interfaceReads, true)) {
                $messages[] = sprintf(
                    '%s declares #[ReadsTable(%s::class)] but %s does not.',
                    $implementationMethodLabel,
                    $tableHelperClass,
                    $interfaceMethodLabel
                );
            }
        }

        foreach ($implementationWrites as $tableHelperClass) {
            if (!in_array($tableHelperClass, $interfaceWrites, true)) {
                $messages[] = sprintf(
                    '%s declares #[WritesTable(%s::class)] but %s does not.',
                    $implementationMethodLabel,
                    $tableHelperClass,
                    $interfaceMethodLabel
                );
            }
        }

        return $messages;
    }

    /**
     * @return array{reads: list<string>, writes: list<string>}
     */
    public function collectDeclaredFromMethodReflection(ReflectionMethod $reflectionMethod): array
    {
        $reads = [];
        $writes = [];

        foreach ($reflectionMethod->getAttributes(ReadsTable::class) as $attribute) {
            $reads[] = $attribute->newInstance()->tableHelperClass;
        }

        foreach ($reflectionMethod->getAttributes(WritesTable::class) as $attribute) {
            $writes[] = $attribute->newInstance()->tableHelperClass;
        }

        return [
            'reads' => $this->normalizeClassList($reads),
            'writes' => $this->normalizeClassList($writes),
        ];
    }

    /**
     * @param callable(Name): string $resolveClassName
     * @param callable(string): bool $isKnownDatabaseHelper
     * @return array{reads: list<string>, writes: list<string>}
     */
    public function collectUsedFromMethodNode(
        ClassMethod $classMethod,
        callable $resolveClassName,
        callable $isKnownDatabaseHelper
    ): array {
        $reads = [];
        $writes = [];

        $nodeFinder = new NodeFinder();

        /** @var list<ClassConstFetch> $classConstFetches */
        $classConstFetches = $nodeFinder->findInstanceOf($classMethod, ClassConstFetch::class);
        foreach ($classConstFetches as $classConstFetch) {
            if (!$classConstFetch->class instanceof Name) {
                continue;
            }

            if (!$classConstFetch->name instanceof Identifier) {
                continue;
            }

            $constantName = $classConstFetch->name->toString();
            $resolvedClassName = $resolveClassName($classConstFetch->class);

            if (!str_starts_with($resolvedClassName, self::DATABASE_NAMESPACE_PREFIX)) {
                continue;
            }

            if (in_array($constantName, self::READ_CONSTANTS, true)) {
                $reads[] = $resolvedClassName;
            }

            if (in_array($constantName, self::WRITE_CONSTANTS, true)) {
                $writes[] = $resolvedClassName;
            }
        }

        /** @var list<String_> $stringNodes */
        $stringNodes = $nodeFinder->findInstanceOf($classMethod, String_::class);
        foreach ($stringNodes as $stringNode) {
            $this->mergeExtractedTables(
                $this->sqlTableReferenceExtractor->extract($stringNode->value),
                $isKnownDatabaseHelper,
                $reads,
                $writes
            );
        }

        /** @var list<Encapsed|InterpolatedString> $encapsedNodes */
        $encapsedNodes = array_merge(
            $nodeFinder->findInstanceOf($classMethod, Encapsed::class),
            $nodeFinder->findInstanceOf($classMethod, InterpolatedString::class)
        );
        foreach ($encapsedNodes as $encapsedNode) {
            $literalParts = [];
            foreach ($encapsedNode->parts as $part) {
                if ($part instanceof EncapsedStringPart) {
                    $literalParts[] = $part->value;
                } elseif ($part instanceof String_) {
                    $literalParts[] = $part->value;
                } elseif (property_exists($part, 'value') && is_string($part->value)) {
                    // PhpParser 5 InterpolatedStringPart (and similar)
                    $literalParts[] = $part->value;
                } else {
                    // Variable interpolation — leave a space so tokens do not glue together
                    $literalParts[] = ' ';
                }
            }
            $this->mergeExtractedTables(
                $this->sqlTableReferenceExtractor->extract(implode('', $literalParts)),
                $isKnownDatabaseHelper,
                $reads,
                $writes
            );
        }

        return [
            'reads' => $this->normalizeClassList($reads),
            'writes' => $this->normalizeClassList($writes),
        ];
    }

    /**
     * @param array{reads: list<string>, writes: list<string>} $extracted
     * @param callable(string): bool $isKnownDatabaseHelper
     * @param list<string> $reads
     * @param list<string> $writes
     */
    private function mergeExtractedTables(
        array $extracted,
        callable $isKnownDatabaseHelper,
        array &$reads,
        array &$writes
    ): void {
        foreach ($extracted['reads'] as $tableName) {
            $helperClass = self::DATABASE_NAMESPACE_PREFIX . $tableName;
            if ($isKnownDatabaseHelper($helperClass)) {
                $reads[] = $helperClass;
            }
        }
        foreach ($extracted['writes'] as $tableName) {
            $helperClass = self::DATABASE_NAMESPACE_PREFIX . $tableName;
            if ($isKnownDatabaseHelper($helperClass)) {
                $writes[] = $helperClass;
            }
        }
    }

    public function isValidDatabaseTableHelperClass(string $tableHelperClass): bool
    {
        return str_starts_with($tableHelperClass, self::DATABASE_NAMESPACE_PREFIX)
            && $tableHelperClass !== self::DATABASE_NAMESPACE_PREFIX
            && !str_contains(substr($tableHelperClass, strlen(self::DATABASE_NAMESPACE_PREFIX)), '\\');
    }

    /**
     * @param list<string> $enforcedDirectories
     */
    public function isPathInEnforcedDirectories(string $fileName, array $enforcedDirectories): bool
    {
        $normalizedFileName = str_replace('\\', '/', $fileName);

        foreach ($enforcedDirectories as $directory) {
            $normalizedDirectory = trim(str_replace('\\', '/', $directory), '/');
            if ($normalizedDirectory === '') {
                continue;
            }

            if (str_contains($normalizedFileName, '/' . $normalizedDirectory . '/')) {
                return true;
            }

            if (str_ends_with($normalizedFileName, '/' . $normalizedDirectory)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<string> $classNames
     * @return list<string>
     */
    private function normalizeClassList(array $classNames): array
    {
        $normalized = [];
        foreach ($classNames as $className) {
            $normalized[] = ltrim($className, '\\');
        }

        $normalized = array_values(array_unique($normalized));
        sort($normalized);

        return $normalized;
    }
}
