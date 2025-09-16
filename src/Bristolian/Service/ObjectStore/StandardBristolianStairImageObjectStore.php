<?php

namespace Bristolian\Service\ObjectStore;

use Bristolian\Filesystem\BristolStairsFilesystem;

/**
 * The standard implementation for storing files that are uploaded to rooms.
 */
class StandardBristolianStairImageObjectStore implements BristolianStairImageObjectStore
{
    public function __construct(private BristolStairsFilesystem $bristolStairsFilesystem)
    {
    }

    public function upload(string $filename, string $contents): void
    {
        $this->bristolStairsFilesystem->write($filename, $contents);
    }
}
