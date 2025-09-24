<?php

namespace Bristolian\Repo\RoomFileObjectInfoRepo;

use Bristolian\UploadedFiles\UploadedFile;

/**
 * Stores information about a file in the local database.
 * The actual file will be stored in an object store.
 */
interface RoomFileObjectInfoRepo
{
    /**
     * Stores information about a file that a user is uploading.
     * This happens before the file is put in the object store.
     *
     * @param string $normalized_filename
     * @param UploadedFile $uploadedFile
     * @return string The 'file_storage_id'
     */
    public function createRoomFileObjectInfo(
        string $user_id,
        string $normalized_filename,
        UploadedFile $uploadedFile,
    ): string;

    /**
     * Update the file to have a status of uploaded.
     * @param string $file_storage_id
     * @return void
     */
    public function setRoomFileObjectUploaded(string $file_storage_id): void;
}
