<?php

namespace Bristolian\Repo\BristolStairImageStorageInfoRepo;

use Bristolian\Model\StoredFile;
use Bristolian\UploadedFiles\UploadedFile;
use Ramsey\Uuid\Uuid;

class FakeBristolStairImageStorageInfoRepo implements BristolStairImageStorageInfoRepo
{
    /**
     * @var StoredFile[]
     */
    private array $storedFileInfo = [];

    public function storeFileInfo(
        string $user_id,
        string $normalized_filename,
        UploadedFile $uploadedFile,
    ): string {

        $datetime = new \DateTimeImmutable();
        $uuid = Uuid::uuid7();
        $id = $uuid->toString();
        $this->storedFileInfo[$id] = new StoredFile(
            $id,
            $normalized_filename,
            $original_filename = $uploadedFile->getOriginalName(),
            $state = FileState::INITIAL->value,
            $size = $uploadedFile->getSize(),
            $user_id,
            $created_at = $datetime
        );

        return $id;
    }

    public function setUploaded(string $file_storage_id): void
    {
        // TODO - should throw an exception if $file_storage_id is invalid
    }

    /**
     * @return StoredFile[]
     */
    public function getStoredFileInfo(): array
    {
        return $this->storedFileInfo;
    }
}
