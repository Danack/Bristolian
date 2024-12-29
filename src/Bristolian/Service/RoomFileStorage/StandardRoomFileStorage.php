<?php

namespace Bristolian\Service\RoomFileStorage;

use Bristolian\Filesystem\RoomFileFilesystem;
use Bristolian\Repo\FileStorageInfoRepo\FileStorageInfoRepo;
use Bristolian\Service\FileStorageProcessor\ObjectStoredFileInfo;
use Bristolian\Service\FileStorageProcessor\UploadError;
use Bristolian\UploadedFiles\UploadedFile;
use Bristolian\Repo\RoomFileRepo\RoomFileRepo;
use Bristolian\Service\FileStorageProcessor\FileStorageProcessor;
//use Bristolian\Service\FileUploader\FileObjectStore;
use Bristolian\Service\ObjectStore\RoomFileObjectStore;

/**
 * Stores files that have been uploaded for use in a Room.
 *
 */
class StandardRoomFileStorage implements RoomFileStorage
{
    public function __construct(
        private FileStorageProcessor $fileStorageProcessor,
        private RoomFileObjectStore  $fileObjectStore,
        private RoomFileRepo         $roomFileRepo
    ) {
    }

    /**
     * @param string $user_id
     * @param string $room_id
     * @param UploadedFile $uploadedFile
     * @return ObjectStoredFileInfo|UploadError
     */
    public function storeFileForRoomAndUser(
        string $user_id,
        string $room_id,
        UploadedFile $uploadedFile
    ): ObjectStoredFileInfo|UploadError {

        $result = $this->fileStorageProcessor->storeFileForUser(
            $user_id,
            $uploadedFile,
            get_supported_room_file_extensions(),
            $this->fileObjectStore
        );

        if ($result instanceof UploadError) {
            return $result;
        }

        $this->roomFileRepo->addFileToRoom(
            $result->fileStorageId,
            $room_id
        );

        return $result;
    }
}
