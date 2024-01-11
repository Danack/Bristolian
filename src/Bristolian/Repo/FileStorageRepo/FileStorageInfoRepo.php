<?php

namespace Bristolian\Repo\FileStorageRepo;

use Bristolian\Repo\FileStorageRepo\FileType;
use Bristolian\Model\Meme;

interface FileStorageInfoRepo
{
    public function createEntry(
        string $user_id,
        string $filename,
        FileType $filetype
    ): string;

    public function setUploaded(string $file_storage_id): void;

    /**
     * @param string $user_id
     * @return Meme[]
     */
    public function listMemesForUser(string $user_id);
}
