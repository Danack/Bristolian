<?php

namespace Bristolian\Service\RoomFileStorage;

use Bristolian\Repo\RoomFileObjectInfoRepo\RoomFileObjectInfoRepo;
use Bristolian\Repo\RoomFileRepo\RoomFileRepo;
use Bristolian\Service\ObjectStore\RoomFileObjectStore;
use Bristolian\UploadedFiles\UploadedFile;
use Ramsey\Uuid\Uuid;

/**
 * Stores files that have been uploaded for use in a Room.
 *
 */
class StandardRoomFileStorage implements RoomFileStorage
{
    public function __construct(
        private RoomFileObjectStore    $fileObjectStore,
        private RoomFileObjectInfoRepo $roomFileObjectInfoRepo,
        private RoomFileRepo           $roomFileRepo
    ) {
    }

    /**
     * Store the file in object storage, and create an entry in
     * the 'stored_file' database
     * @param string $user_id
     * @param string $room_id
     * @param UploadedFile $uploadedFile
     * @return string|UploadError
     */
    public function storeFileForRoomAndUser(
        string $user_id,
        string $room_id,
        UploadedFile $uploadedFile
    ): string|UploadError {

        $contents = @file_get_contents($uploadedFile->getTmpName());
        if ($contents === false) {
            return UploadError::uploadedFileUnreadable();
        }

        // Normalize extension.
        $extension = normalize_file_extension(
            $uploadedFile->getOriginalName(),
            $contents,
            get_supported_room_file_extensions()
        );

        if ($extension === null) {
            return UploadError::unsupportedFileType();
        }

        $uuid = Uuid::uuid7();
        $normalized_filename = $uuid->toString() . "." . $extension;

        $fileStorageId = $this->roomFileObjectInfoRepo->createRoomFileObjectInfo(
            $user_id,
            $normalized_filename,
            $uploadedFile
        );

        // TODO - change to stream copying to avoid large memory use.
        $this->fileObjectStore->upload($normalized_filename, $contents);

        $this->roomFileObjectInfoRepo->setRoomFileObjectUploaded($fileStorageId);

        // File has been stored, add it to the room.
        $this->roomFileRepo->addFileToRoom(
            $fileStorageId,
            $room_id
        );

        return $fileStorageId;
    }
}
