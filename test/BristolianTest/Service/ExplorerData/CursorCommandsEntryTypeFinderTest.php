<?php

declare(strict_types=1);

namespace BristolianTest\Service\ExplorerData;

use Bristolian\Service\ExplorerData\CursorCommandsEntryTypeFinder;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class CursorCommandsEntryTypeFinderTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\ExplorerData\CursorCommandsEntryTypeFinder::__construct
     * @covers \Bristolian\Service\ExplorerData\CursorCommandsEntryTypeFinder::getEntryTypeKey
     * @covers \Bristolian\Service\ExplorerData\CursorCommandsEntryTypeFinder::findEntries
     */
    public function test_findEntries_reads_project_command_meta(): void
    {
        $finder = new CursorCommandsEntryTypeFinder();

        $this->assertSame('cursor_commands', $finder->getEntryTypeKey());

        $entries = $finder->findEntries();

        $this->assertNotEmpty($entries);
        $this->assertSame(['id', 'label', 'file', 'priority'], array_keys($entries[0]));
        $this->assertSame('improve_test_coverage', $entries[0]['id']);
        $this->assertSame('Improve test coverage', $entries[0]['label']);
        $this->assertSame('.cursor/commands/improve_test_coverage.md', $entries[0]['file']);
        $this->assertSame(3, $entries[0]['priority']);

        $ids = array_column($entries, 'id');
        $this->assertContains('commit', $ids);
        $this->assertContains('finalise_work', $ids);
        $this->assertContains('lessons_learned', $ids);
        $this->assertSame($ids, array_values(array_unique($ids)));

        $priorities = array_column($entries, 'priority');
        $sortedPriorities = $priorities;
        sort($sortedPriorities);
        $this->assertSame($sortedPriorities, $priorities);
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\CursorCommandsEntryTypeFinder::findEntries
     */
    public function test_findEntries_sorts_by_priority_then_meta_list_order(): void
    {
        $directoryPath = $this->createTemporaryCommandsDirectory([
            'alpha.md' => "# Alpha\n",
            'beta.md' => "# Beta\n",
            'gamma.md' => "# Gamma\n",
        ], [
            'commands' => [
                ['file' => 'gamma.md', 'name' => 'Gamma', 'priority' => 2],
                ['file' => 'beta.md', 'name' => 'Beta', 'priority' => 1],
                ['file' => 'alpha.md', 'name' => 'Alpha', 'priority' => 2],
            ],
        ]);

        $finder = new CursorCommandsEntryTypeFinder($directoryPath);
        $entries = $finder->findEntries();

        $this->assertSame(['beta', 'gamma', 'alpha'], array_column($entries, 'id'));
        $this->assertSame([1, 2, 2], array_column($entries, 'priority'));
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\CursorCommandsEntryTypeFinder::findEntries
     */
    public function test_findEntries_throws_when_markdown_file_missing_from_meta(): void
    {
        $directoryPath = $this->createTemporaryCommandsDirectory([
            'known.md' => "# Known\n",
            'orphan.md' => "# Orphan\n",
        ], [
            'commands' => [
                ['file' => 'known.md', 'name' => 'Known', 'priority' => 1],
            ],
        ]);

        $finder = new CursorCommandsEntryTypeFinder($directoryPath);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('orphan.md');
        $finder->findEntries();
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\CursorCommandsEntryTypeFinder::findEntries
     */
    public function test_findEntries_throws_when_meta_lists_unknown_markdown_file(): void
    {
        $directoryPath = $this->createTemporaryCommandsDirectory([
            'known.md' => "# Known\n",
        ], [
            'commands' => [
                ['file' => 'known.md', 'name' => 'Known', 'priority' => 1],
                ['file' => 'missing.md', 'name' => 'Missing', 'priority' => 2],
            ],
        ]);

        $finder = new CursorCommandsEntryTypeFinder($directoryPath);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('unknown markdown file');
        $finder->findEntries();
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\CursorCommandsEntryTypeFinder::findEntries
     */
    public function test_findEntries_throws_when_name_missing(): void
    {
        $directoryPath = $this->createTemporaryCommandsDirectory([
            'known.md' => "# Known\n",
        ], [
            'commands' => [
                ['file' => 'known.md', 'priority' => 1],
            ],
        ]);

        $finder = new CursorCommandsEntryTypeFinder($directoryPath);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('missing a non-empty "name"');
        $finder->findEntries();
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\CursorCommandsEntryTypeFinder::findEntries
     */
    public function test_findEntries_throws_when_priority_missing(): void
    {
        $directoryPath = $this->createTemporaryCommandsDirectory([
            'known.md' => "# Known\n",
        ], [
            'commands' => [
                ['file' => 'known.md', 'name' => 'Known'],
            ],
        ]);

        $finder = new CursorCommandsEntryTypeFinder($directoryPath);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('missing an integer "priority"');
        $finder->findEntries();
    }

    /**
     * @param array<string, string> $markdownFiles
     * @param array{commands: list<array<string, mixed>>} $meta
     */
    private function createTemporaryCommandsDirectory(array $markdownFiles, array $meta): string
    {
        $directoryPath = sys_get_temp_dir() . '/cursor-commands-test-' . uniqid('', true);
        mkdir($directoryPath, 0755, true);

        foreach ($markdownFiles as $filename => $contents) {
            file_put_contents($directoryPath . '/' . $filename, $contents);
        }

        file_put_contents(
            $directoryPath . '/command.meta.json',
            json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
        );

        return $directoryPath;
    }
}
