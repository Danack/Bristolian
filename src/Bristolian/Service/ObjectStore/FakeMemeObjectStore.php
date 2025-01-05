<?php

namespace Bristolian\Service\ObjectStore;

use Bristolian\Filesystem\MemeFilesystem;

/**
 * The standard implementation for storing files that are uploaded to rooms.
 */
class FakeMemeObjectStore implements MemeObjectStore
{
    /**
     * @var array<string, string>
     */
    private array $storedFiles = [];


    public function __construct()
    {
    }

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
