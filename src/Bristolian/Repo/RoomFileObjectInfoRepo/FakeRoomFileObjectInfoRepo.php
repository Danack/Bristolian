<?php

namespace Bristolian\Repo\RoomFileObjectInfoRepo;

use Bristolian\UploadedFiles\UploadedFile;
use Bristolian\Model\Generated\RoomFileObjectInfo;
use Ramsey\Uuid\Uuid;

class FakeRoomFileObjectInfoRepo implements RoomFileObjectInfoRepo
{
    /**
     * @var RoomFileObjectInfo[]
     */
    private array $storedFileInfo = [];

    public function createRoomFileObjectInfo(
        string $user_id,
        string $normalized_filename,
        UploadedFile $uploadedFile,
    ): string {

        $datetime = new \DateTimeImmutable();
        $uuid = Uuid::uuid7();
        $id = $uuid->toString();
        $this->storedFileInfo[$id] = new RoomFileObjectInfo(
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

    public function setRoomFileObjectUploaded(string $file_storage_id): void
    {
        // TODO - should throw an exception if $file_storage_id is invalid
    }

    /**
     * @return RoomFileObjectInfo[]
     */
    public function getStoredFileInfo(): array
    {
        return $this->storedFileInfo;
    }
}
