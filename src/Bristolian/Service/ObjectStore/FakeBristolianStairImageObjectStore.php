<?php

declare(strict_types=1);

namespace Bristolian\Service\ObjectStore;

/**
 * Fake implementation of BristolianStairImageObjectStore for testing.
 * Stores uploads in memory.
 */
class FakeBristolianStairImageObjectStore implements BristolianStairImageObjectStore
{
    /**
     * @var array<string, string>
     */
    private array $storedFiles = [];

    public function upload(string $filename, string $contents): void
    {
        $this->storedFiles[$filename] = $contents;
    }

    public function hasFile(string $filename): bool
    {
        return isset($this->storedFiles[$filename]);
    }

    public function getFileContents(string $filename): string
    {
        return $this->storedFiles[$filename];
    }
}
