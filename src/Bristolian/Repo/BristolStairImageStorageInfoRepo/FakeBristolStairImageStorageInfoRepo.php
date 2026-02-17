<?php

namespace Bristolian\Repo\BristolStairImageStorageInfoRepo;

use Bristolian\Model\Generated\StairImageObjectInfo as BristolStairImageFile;
use Bristolian\UploadedFiles\UploadedFile;
use Ramsey\Uuid\Uuid;

class FakeBristolStairImageStorageInfoRepo implements BristolStairImageStorageInfoRepo
{
    /**
     * @var BristolStairImageFile[]
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
        
        $this->storedFileInfo[$id] = new BristolStairImageFile(
            id: $id,
            user_id: $user_id,
            normalized_name: $normalized_filename,
            original_filename: $uploadedFile->getOriginalName(),
            size: $uploadedFile->getSize(),
            state: FileState::INITIAL->value,
            created_at: $datetime
        );

        return $id;
    }

    public function getById(string $bristol_stairs_image_id): BristolStairImageFile|null
    {
        return $this->storedFileInfo[$bristol_stairs_image_id] ?? null;
    }

    public function getByNormalizedName(string $normalized_name): BristolStairImageFile|null
    {
        foreach ($this->storedFileInfo as $fileInfo) {
            if ($fileInfo->normalized_name === $normalized_name) {
                return $fileInfo;
            }
        }
        
        return null;
    }

    public function setUploaded(string $file_storage_id): void
    {
        if (!isset($this->storedFileInfo[$file_storage_id])) {
            throw new \Exception("Failed to update uploaded file.");
        }

        $existingFile = $this->storedFileInfo[$file_storage_id];
        
        // Create a new instance with updated state
        $this->storedFileInfo[$file_storage_id] = new BristolStairImageFile(
            id: $existingFile->id,
            user_id: $existingFile->user_id,
            normalized_name: $existingFile->normalized_name,
            original_filename: $existingFile->original_filename,
            size: $existingFile->size,
            state: FileState::UPLOADED->value,
            created_at: $existingFile->created_at
        );
    }

    /**
     * @return BristolStairImageFile[]
     */
    public function getStoredFileInfo(): array
    {
        return $this->storedFileInfo;
    }
}
