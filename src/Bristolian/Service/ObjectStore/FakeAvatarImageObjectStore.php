<?php

namespace Bristolian\Service\ObjectStore;

/**
 * Fake implementation of AvatarImageObjectStore for testing.
 * Stores uploads in memory.
 */
class FakeAvatarImageObjectStore implements AvatarImageObjectStore
{
    /**
     * @var array<string, string>
     */
    private array $storedFiles = [];

    public function upload(string $filename, string $contents): void
    {
        $this->storedFiles[$filename] = $contents;
    }

    public function hasFile(string $name): bool
    {
        return isset($this->storedFiles[$name]);
    }

    public function getFileContents(string $name): string
    {
        return $this->storedFiles[$name];
    }

    /**
     * @return array<string, string>
     */
    public function getStoredFiles(): array
    {
        return $this->storedFiles;
    }
}
