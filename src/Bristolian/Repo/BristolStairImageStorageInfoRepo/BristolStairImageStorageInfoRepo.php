<?php

namespace Bristolian\Repo\BristolStairImageStorageInfoRepo;

use Bristolian\UploadedFiles\UploadedFile;
use Bristolian\Database\stored_stair_image_file;
use Bristolian\Model\BristolStairImageFile;

/**
 * Stores information about an image in the local database.
 * The actual image will be stored in an object store.
 */
interface BristolStairImageStorageInfoRepo
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

    function getById(string $bristol_stairs_image_id): BristolStairImageFile|null;

    public function setUploaded(string $file_storage_id): void;
}
