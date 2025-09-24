<?php

namespace Bristolian\Service\BristolStairImageStorage;

use Bristolian\Service\ObjectStore\FileObjectStore;
use Bristolian\UploadedFiles\UploadedFile;
use Ramsey\Uuid\Uuid;

class FakeWorksBristolStairImageStorage implements BristolStairImageStorage
{
    /**
     * @param string $user_id
     * @param UploadedFile $uploadedFile
     * @param string[] $allowedExtensions
     * @return string|UploadError
     */
    public function storeFileForUser(
        string $user_id,
        UploadedFile $uploadedFile,
        array $allowedExtensions,
    ):  string|UploadError {
        $uuid = Uuid::uuid7();
        $normalized_filename = $uuid->toString() . ".pdf";

        $uuid = Uuid::uuid7();
        $fileStorageId = $uuid->toString();

        return $fileStorageId;
    }
}
