<?php

namespace Bristolian\Service\ObjectStore;

use Bristolian\Filesystem\AvatarImageFilesystem;

/**
 * The standard implementation for storing avatar images.
 */
class StandardAvatarImageObjectStore implements AvatarImageObjectStore
{
    public function __construct(private AvatarImageFilesystem $avatarImageFilesystem)
    {
    }

    public function upload(string $filename, string $contents): void
    {
        $this->avatarImageFilesystem->write($filename, $contents);
    }
}

