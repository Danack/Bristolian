<?php

namespace Bristolian\Service\ObjectStore;

use Bristolian\Filesystem\MemeFilesystem;

/**
 * The standard implementation for storing memes.
 */
class StandardMemeObjectStore implements MemeObjectStore
{
    public function __construct(private MemeFilesystem $memeFilesystem)
    {
    }

    public function upload(string $filename, string $contents): void
    {
        $this->memeFilesystem->write($filename, $contents);
    }
}
