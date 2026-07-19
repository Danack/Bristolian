<?php

namespace Bristolian\Repo\AvatarImageStorageInfoRepo;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use Bristolian\Database\avatar_image_object_info;
use Bristolian\Model\Types\AvatarImageFile;
use Bristolian\UploadedFiles\UploadedFile;

/**
 * Stores information about an avatar image in the local database.
 * The actual image will be stored in an object store.
 */
interface AvatarImageStorageInfoRepo
{
    /**
     * Stores information about a file that a user is uploading.
     * This happens before the file is put in the object store.
     *
     * @param string $normalized_filename
     * @param UploadedFile $uploadedFile
     * @return string The 'file_storage_id'
     */
    #[WritesTable(avatar_image_object_info::class)]
    public function storeFileInfo(
        string $user_id,
        string $normalized_filename,
        UploadedFile $uploadedFile,
    ): string;

    #[ReadsTable(avatar_image_object_info::class)]
    public function getById(string $avatar_image_id): AvatarImageFile|null;

    #[ReadsTable(avatar_image_object_info::class)]
    public function getByNormalizedName(string $normalized_name): AvatarImageFile|null;

    #[WritesTable(avatar_image_object_info::class)]
    public function setUploaded(string $file_storage_id): void;
}
