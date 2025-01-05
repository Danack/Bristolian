<?php

namespace Bristolian\Service\MemeStorageProcessor;

use Bristolian\Service\ObjectStore\FileObjectStore;
use Bristolian\UploadedFiles\UploadedFile;
use Ramsey\Uuid\Uuid;

class FakeWorksMemeStorageProcessor implements MemeStorageProcessor
{
    /**
     * @param string $user_id
     * @param UploadedFile $uploadedFile
     * @param string[] $allowedExtensions
     * @param FileObjectStore $fileObjectStore
     * @return ObjectStoredMeme|UploadError
     */
    public function storeMemeForUser(
        string $user_id,
        UploadedFile $uploadedFile,
        array $allowedExtensions,
        FileObjectStore $fileObjectStore
    ):  ObjectStoredMeme|UploadError {
        $uuid = Uuid::uuid7();
        $normalized_filename = $uuid->toString() . ".pdf";

        $uuid = Uuid::uuid7();
        $fileStorageId = $uuid->toString();

        return new ObjectStoredMeme($normalized_filename, $fileStorageId);
    }
}
