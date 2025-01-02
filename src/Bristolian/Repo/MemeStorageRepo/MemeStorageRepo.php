<?php

namespace Bristolian\Repo\MemeStorageRepo;

use Bristolian\Model\Meme;
use Bristolian\UploadedFiles\UploadedFile;

/**
 * Stores information about a meme file in the local database.
 * The actual file will be stored in an object store.
 */
interface MemeStorageRepo
{
    /**
     * Stores information about a file that a user is uploading.
     * This happens before the file is put in the object store.
     *
     * @param string $normalized_filename
     * @param UploadedFile $uploadedFile
     * @return string The 'file_storage_id'
     */
    public function storeMeme(
        string $user_id,
        string $normalized_filename,
        UploadedFile $uploadedFile,
    ): string;

    /**
     * @return Meme[]
     */
    public function listMemesForUser(string $user_id): array;

    public function setUploaded(string $meme_id): void;
}
