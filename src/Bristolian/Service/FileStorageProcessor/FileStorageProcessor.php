<?php

namespace Bristolian\Service\FileStorageProcessor;

use Bristolian\Service\ObjectStore\FileObjectStore;
use Bristolian\UploadedFiles\UploadedFile;

/**
 * Processes a file, by checking it is valid, stores information
 * about it, uploads it to an object store, and then saves the fact
 * that it is uploaded to object store correctly.
 */
interface FileStorageProcessor
{
    /**
     * Stores a file in object storage somewhere, and stores information
     * about the stored file.
     *
     * @param string $user_id
     * @param UploadedFile $UploadedFile
     * @param array $allowedExtensions
     * @return array{0:string, 1:null}|array{0:null, 1:string }
     */
    public function storeFileForUser(
        string $user_id,
        UploadedFile $UploadedFile,
        array $allowedExtensions,
        FileObjectStore $fileObjectStore
    ): ObjectStoredFileInfo|UploadError;
}
