<?php

namespace Bristolian\Service\BristolStairImageStorageProcessor;

use Bristolian\Service\ObjectStore\FileObjectStore;
use Bristolian\UploadedFiles\UploadedFile;
use Ramsey\Uuid\Uuid;

class FakeWorksBristolStairImageStorageProcessor implements BristolStairImageStorageProcessor
{
    /**
     * @param string $user_id
     * @param UploadedFile $uploadedFile
     * @param string[] $allowedExtensions
     * @param FileObjectStore $fileObjectStore
     * @return ObjectStoredFileInfo|UploadError
     */
    public function storeFileForUser(
        string $user_id,
        UploadedFile $uploadedFile,
        array $allowedExtensions,
        FileObjectStore $fileObjectStore
    ):  ObjectStoredFileInfo|UploadError {
        $uuid = Uuid::uuid7();
        $normalized_filename = $uuid->toString() . ".pdf";

        $uuid = Uuid::uuid7();
        $fileStorageId = $uuid->toString();

        return new ObjectStoredFileInfo($normalized_filename, $fileStorageId);
    }
}
