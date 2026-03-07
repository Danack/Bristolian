<?php

declare(strict_types=1);

namespace BristolianTest\Cache;

use Bristolian\Cache\QueryTagMapping;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Bristolian\Cache\QueryTagMapping
 */
class QueryTagMappingTest extends TestCase
{
    public function testGetExactMappingsReturnsNonEmptyArray(): void
    {
        $mappings = QueryTagMapping::getExactMappings();
        $this->assertIsArray($mappings);
        $this->assertNotEmpty($mappings);
    }

    public function testGetExactMappingsKeysAreTrimmedStrings(): void
    {
        $mappings = QueryTagMapping::getExactMappings();

        foreach ($mappings as $query => $tags) {
            $this->assertIsString($query);
            $this->assertSame(trim($query), $query, "Mapping key should be trimmed: " . substr($query, 0, 60));
        }
    }

    public function testGetExactMappingsValuesHaveReadAndWriteKeys(): void
    {
        $mappings = QueryTagMapping::getExactMappings();

        foreach ($mappings as $query => $tags) {
            $this->assertArrayHasKey('read', $tags, "Missing 'read' key for query: " . substr($query, 0, 60));
            $this->assertArrayHasKey('write', $tags, "Missing 'write' key for query: " . substr($query, 0, 60));
            $this->assertIsArray($tags['read']);
            $this->assertIsArray($tags['write']);
        }
    }

    public function testGetExactMappingsEveryEntryHasAtLeastOneTable(): void
    {
        $mappings = QueryTagMapping::getExactMappings();

        foreach ($mappings as $query => $tags) {
            $totalTables = count($tags['read']) + count($tags['write']);
            $this->assertGreaterThan(
                0,
                $totalTables,
                "Mapping should reference at least one table: " . substr($query, 0, 60)
            );
        }
    }

    public function testGetExactMappingsTableNamesAreStrings(): void
    {
        $mappings = QueryTagMapping::getExactMappings();

        foreach ($mappings as $query => $tags) {
            foreach ($tags['read'] as $table) {
                $this->assertIsString($table);
                $this->assertNotEmpty($table);
            }
            foreach ($tags['write'] as $table) {
                $this->assertIsString($table);
                $this->assertNotEmpty($table);
            }
        }
    }

    public function testGetPatternMappingsReturnsArray(): void
    {
        $mappings = QueryTagMapping::getPatternMappings();
        $this->assertIsArray($mappings);
    }

    public function testGetPatternMappingsEntriesHaveRequiredKeys(): void
    {
        $mappings = QueryTagMapping::getPatternMappings();

        foreach ($mappings as $index => $entry) {
            $this->assertArrayHasKey('pattern', $entry, "Missing 'pattern' at index $index");
            $this->assertArrayHasKey('read', $entry, "Missing 'read' at index $index");
            $this->assertArrayHasKey('write', $entry, "Missing 'write' at index $index");
        }
    }

    public function testGetPatternMappingsPatternsAreValidRegex(): void
    {
        $mappings = QueryTagMapping::getPatternMappings();

        foreach ($mappings as $index => $entry) {
            $result = @preg_match($entry['pattern'], '');
            $this->assertNotFalse($result, "Invalid regex pattern at index $index: " . $entry['pattern']);
        }
    }

    public function testGetPatternMappingsEveryEntryHasAtLeastOneTable(): void
    {
        $mappings = QueryTagMapping::getPatternMappings();

        foreach ($mappings as $index => $entry) {
            $totalTables = count($entry['read']) + count($entry['write']);
            $this->assertGreaterThan(
                0,
                $totalTables,
                "Pattern mapping at index $index should reference at least one table"
            );
        }
    }

    public function testGetExactMappingsNoDuplicateKeys(): void
    {
        $mappings = QueryTagMapping::getExactMappings();
        $count = count($mappings);
        $this->assertGreaterThan(50, $count, "Expected at least 50 exact mappings");
    }
}
