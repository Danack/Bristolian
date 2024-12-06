<?php

namespace Bristolian\Service\FileStorageProcessor;

use Bristolian\Service\ObjectStore\FileObjectStore;
use Bristolian\UploadedFiles\UploadedFile;
use Ramsey\Uuid\Uuid;

class FakeWorksFileStorageProcessor implements FileStorageProcessor
{
    public function storeFileForUser(
        string $user_id,
        UploadedFile $UploadedFile,
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
