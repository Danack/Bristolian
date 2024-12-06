<?php

namespace Bristolian\Repo\FileStorageInfoRepo;

use \Bristolian\UploadedFiles\UploadedFile;

/**
 * Stores information about a file in the local database.
 */
interface FileStorageInfoRepo
{
    /**
     * Stores information about a file that a user is uploading.
     * This happens before the file is put in the object store.
     *
     * @param string $normalized_filename
     * @param UploadedFile $uploadedFile
     * @return string The 'file_storage_id'
     */
    public function storeFileInfo(
        string $user_id,
        string $normalized_filename,
        UploadedFile $uploadedFile,
    ): string;

    public function setUploaded(string $file_storage_id): void;
}
