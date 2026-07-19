<?php

namespace Bristolian\Repo\BristolStairImageStorageInfoRepo;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use Bristolian\Database\stair_image_object_info;
use Bristolian\Model\Generated\StairImageObjectInfo as BristolStairImageFile;
use Bristolian\UploadedFiles\UploadedFile;

//use BristolStairImageFile;

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
    #[WritesTable(stair_image_object_info::class)]
    public function storeFileInfo(
        string $user_id,
        string $normalized_filename,
        UploadedFile $uploadedFile,
    ): string;

    #[ReadsTable(stair_image_object_info::class)]
    public function getById(string $bristol_stairs_image_id): BristolStairImageFile|null;

    #[ReadsTable(stair_image_object_info::class)]
    public function getByNormalizedName(string $normalized_name): BristolStairImageFile|null;

    #[WritesTable(stair_image_object_info::class)]
    public function setUploaded(string $file_storage_id): void;
}
