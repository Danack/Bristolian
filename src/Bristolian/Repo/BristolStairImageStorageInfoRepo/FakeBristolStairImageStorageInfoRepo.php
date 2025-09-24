<?php

namespace Bristolian\Repo\BristolStairImageStorageInfoRepo;

use Bristolian\Model\BristolStairImageFile;
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

    public function getById(string $bristol_stairs_image_id): BristolStairImageFile|null
    {
        throw new \Exception("Implement getById() method.");
    }

    public function getByNormalizedName(string $normalized_name): BristolStairImageFile|null
    {
        throw new \Exception("Implement getByNormalizedName() method.");
    }


    public function setUploaded(string $file_storage_id): void
    {
        throw new \Exception("Implement setUploaded() method.");
    }

    /**
     * @return StoredFile[]
     */
    public function getStoredFileInfo(): array
    {
        return $this->storedFileInfo;
    }
}
