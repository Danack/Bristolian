<?php

declare(strict_types=1);

namespace Bristolian\Service\ExplorerData;

/**
 * Cursor slash-command markdown under .cursor/commands/, driven by command.meta.json.
 *
 * Generation fails if meta is missing fields, lists unknown files, or omits a .md file on disk.
 */
class CursorCommandsEntryTypeFinder implements EntryTypeFinder
{
    private const COMMANDS_DIRECTORY = '.cursor/commands';

    private const META_FILENAME = 'command.meta.json';

    private readonly string $commandsDirectoryPath;

    public function __construct(?string $commandsDirectoryPath = null)
    {
        $this->commandsDirectoryPath = $commandsDirectoryPath
            ?? (dirname(__DIR__, 4) . '/' . self::COMMANDS_DIRECTORY);
    }

    public function getEntryTypeKey(): string
    {
        return 'cursor_commands';
    }

    public function findEntries(): array
    {
        if (is_dir($this->commandsDirectoryPath) === false) {
            throw new \RuntimeException(
                'Cursor commands directory not found: ' . $this->commandsDirectoryPath
            );
        }

        $markdownFilesOnDisk = $this->listMarkdownFilenames();
        $metaCommands = $this->loadMetaCommands();

        $this->assertMetaCoversDiskExactly($metaCommands, $markdownFilesOnDisk);

        $entries = [];
        $listIndex = 0;

        foreach ($metaCommands as $metaCommand) {
            $filename = $metaCommand['file'];
            $commandId = self::commandIdFromFilename($filename);

            $entries[] = [
                'id' => $commandId,
                'label' => $metaCommand['name'],
                'file' => self::COMMANDS_DIRECTORY . '/' . $filename,
                'priority' => $metaCommand['priority'],
                'list_index' => $listIndex,
            ];
            $listIndex += 1;
        }

        usort(
            $entries,
            static function (array $left, array $right): int {
                if ($left['priority'] !== $right['priority']) {
                    return $left['priority'] <=> $right['priority'];
                }

                return $left['list_index'] <=> $right['list_index'];
            }
        );

        $sortedEntries = [];
        foreach ($entries as $entry) {
            $sortedEntries[] = [
                'id' => $entry['id'],
                'label' => $entry['label'],
                'file' => $entry['file'],
                'priority' => $entry['priority'],
            ];
        }

        return $sortedEntries;
    }

    /**
     * @return list<string>
     */
    private function listMarkdownFilenames(): array
    {
        $filenames = [];

        $directoryEntries = scandir($this->commandsDirectoryPath);
        if ($directoryEntries === false) {
            throw new \RuntimeException(
                'Failed to read Cursor commands directory: ' . $this->commandsDirectoryPath
            );
        }

        foreach ($directoryEntries as $directoryEntry) {
            if ($directoryEntry === '.' || $directoryEntry === '..') {
                continue;
            }

            if (str_ends_with($directoryEntry, '.md') === false) {
                continue;
            }

            $filenames[] = $directoryEntry;
        }

        sort($filenames);

        return $filenames;
    }

    /**
     * @return list<array{file: string, name: string, priority: int}>
     */
    private function loadMetaCommands(): array
    {
        $metaPath = $this->commandsDirectoryPath . '/' . self::META_FILENAME;

        if (is_file($metaPath) === false) {
            throw new \RuntimeException(
                'Cursor commands meta file not found: ' . $metaPath
            );
        }

        $rawContents = file_get_contents($metaPath);
        if ($rawContents === false) {
            throw new \RuntimeException(
                'Failed to read Cursor commands meta file: ' . $metaPath
            );
        }

        $decoded = json_decode($rawContents, true);
        if (is_array($decoded) === false) {
            throw new \RuntimeException(
                'Cursor commands meta file is not valid JSON: ' . $metaPath
            );
        }

        if (array_key_exists('commands', $decoded) === false || is_array($decoded['commands']) === false) {
            throw new \RuntimeException(
                'Cursor commands meta file must contain a "commands" array: ' . $metaPath
            );
        }

        $commands = [];
        $commandIndex = 0;

        foreach ($decoded['commands'] as $commandEntry) {
            $commandIndex += 1;
            $location = self::META_FILENAME . ' commands[' . $commandIndex . ']';

            if (is_array($commandEntry) === false) {
                throw new \RuntimeException(
                    'Cursor command meta entry must be an object at ' . $location
                );
            }

            if (array_key_exists('file', $commandEntry) === false
                || is_string($commandEntry['file']) === false
                || $commandEntry['file'] === ''
            ) {
                throw new \RuntimeException(
                    'Cursor command meta entry is missing a non-empty "file" at ' . $location
                );
            }

            if (array_key_exists('name', $commandEntry) === false
                || is_string($commandEntry['name']) === false
                || trim($commandEntry['name']) === ''
            ) {
                throw new \RuntimeException(
                    'Cursor command meta entry is missing a non-empty "name" for file "'
                    . $commandEntry['file'] . '" at ' . $location
                );
            }

            if (array_key_exists('priority', $commandEntry) === false
                || is_int($commandEntry['priority']) === false
            ) {
                throw new \RuntimeException(
                    'Cursor command meta entry is missing an integer "priority" for file "'
                    . $commandEntry['file'] . '" at ' . $location
                );
            }

            if (str_ends_with($commandEntry['file'], '.md') === false) {
                throw new \RuntimeException(
                    'Cursor command meta "file" must end with .md at ' . $location
                    . ' (got "' . $commandEntry['file'] . '")'
                );
            }

            $commands[] = [
                'file' => $commandEntry['file'],
                'name' => $commandEntry['name'],
                'priority' => $commandEntry['priority'],
            ];
        }

        return $commands;
    }

    /**
     * @param list<array{file: string, name: string, priority: int}> $metaCommands
     * @param list<string> $markdownFilesOnDisk
     */
    private function assertMetaCoversDiskExactly(array $metaCommands, array $markdownFilesOnDisk): void
    {
        $metaFilenames = [];

        foreach ($metaCommands as $metaCommand) {
            $filename = $metaCommand['file'];

            if (in_array($filename, $metaFilenames, true) === true) {
                throw new \RuntimeException(
                    'Cursor command meta lists "' . $filename . '" more than once in '
                    . self::META_FILENAME
                );
            }

            $metaFilenames[] = $filename;

            if (in_array($filename, $markdownFilesOnDisk, true) === false) {
                throw new \RuntimeException(
                    'Cursor command meta lists unknown markdown file "' . $filename
                    . '" (not present in ' . self::COMMANDS_DIRECTORY . ')'
                );
            }
        }

        foreach ($markdownFilesOnDisk as $markdownFilename) {
            if (in_array($markdownFilename, $metaFilenames, true) === false) {
                throw new \RuntimeException(
                    'Cursor commands directory has markdown file "' . $markdownFilename
                    . '" with no entry in ' . self::META_FILENAME
                    . ' (missing name and priority)'
                );
            }
        }
    }

    private static function commandIdFromFilename(string $filename): string
    {
        return substr($filename, 0, -3);
    }
}
