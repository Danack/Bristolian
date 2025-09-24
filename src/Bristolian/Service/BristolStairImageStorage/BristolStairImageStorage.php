<?php

namespace Bristolian\Service\BristolStairImageStorage;

use Bristolian\Service\ObjectStore\FileObjectStore;
use Bristolian\UploadedFiles\UploadedFile;

/**
 * Processes a file, by checking it is valid, stores information
 * about it, uploads it to an object store, and then saves the fact
 * that it is uploaded to object store correctly.
 */
interface BristolStairImageStorage
{
    const BRISTOL_CENTRE_LATITUDE = 51.4536491;
    const BRISTOL_CENTRE_LONGITUDE = -2.5913353;

    /**
     * Stores a file in object storage somewhere, and stores information
     * about the stored file.
     *
     * @param string $user_id
     * @param UploadedFile $uploadedFile
     * @param string[] $allowedExtensions
     * @return string|UploadError
     */
    public function storeFileForUser(
        string $user_id,
        UploadedFile $uploadedFile,
        array $allowedExtensions,
    ): string|UploadError;
}
