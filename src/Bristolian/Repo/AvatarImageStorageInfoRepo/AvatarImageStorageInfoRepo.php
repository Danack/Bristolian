<?php

namespace Bristolian\Repo\AvatarImageStorageInfoRepo;

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
    public function storeFileInfo(
        string $user_id,
        string $normalized_filename,
        UploadedFile $uploadedFile,
    ): string;

    public function getById(string $avatar_image_id): AvatarImageFile|null;

    public function getByNormalizedName(string $normalized_name): AvatarImageFile|null;

    public function setUploaded(string $file_storage_id): void;
}

