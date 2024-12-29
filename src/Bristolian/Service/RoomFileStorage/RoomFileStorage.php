<?php

namespace Bristolian\Service\RoomFileStorage;

use Bristolian\UploadedFiles\UploadedFile;
use Bristolian\Service\FileStorageProcessor\ObjectStoredFileInfo;
use Bristolian\Service\FileStorageProcessor\UploadError;

interface RoomFileStorage
{
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
    ): ObjectStoredFileInfo|UploadError;
}
