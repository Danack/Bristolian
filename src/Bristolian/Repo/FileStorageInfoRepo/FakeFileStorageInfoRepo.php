<?php

namespace Bristolian\Repo\FileStorageInfoRepo;

use Bristolian\UploadedFiles\UploadedFile;
use Ramsey\Uuid\Uuid;

class FakeFileStorageInfoRepo implements FileStorageInfoRepo
{
    private $storedFileInfo = [];

    public function storeFileInfo(
        string $user_id,
        string $normalized_filename,
        string $original_filename,
        UploadedFile $uploadedFile
    ): string {

        $uuid = Uuid::uuid7();
        $id = $uuid->toString();
        $params = [
            'id' => $id,
            'user_id' => $user_id,
            'normalized_filename' => $normalized_filename,
            'size' => $uploadedFile->getSize(),
            'filestate' => FileState::INITIAL->value,
        ];

        $this->storedFileInfo[$id] = $params;
    }

    public function setUploaded(string $file_storage_id): void
    {
        // TODO - should throw an exception if $file_storage_id is invalid
    }

    /**
     * @return array
     */
    public function getStoredFileInfo(): array
    {
        return $this->storedFileInfo;
    }
}
