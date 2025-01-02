<?php

namespace Bristolian\Service\MemeStorageProcessor;

use Bristolian\Service\ObjectStore\MemeObjectStore;
use Bristolian\UploadedFiles\UploadedFile;

/**
 * Processes a file, by checking it is valid, stores information
 * about it, uploads it to an object store, and then saves the fact
 * that it is uploaded to object store correctly.
 */
interface MemeStorageProcessor
{
    /**
     * Stores a mem file in object storage somewhere, and stores information
     * about the stored meme.
     *
     * @param string $user_id
     * @param UploadedFile $uploadedFile
     * @param string[] $allowedExtensions
     * @param MemeObjectStore $fileObjectStore
     * @return ObjectStoredMeme|UploadError
     */
    public function storeMemeForUser(
        string $user_id,
        UploadedFile $uploadedFile,
        array $allowedExtensions,
        MemeObjectStore $fileObjectStore
    ): ObjectStoredMeme|UploadError;
}
