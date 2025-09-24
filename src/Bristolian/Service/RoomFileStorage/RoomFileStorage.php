<?php

namespace Bristolian\Service\RoomFileStorage;

use Bristolian\Service\FileStorageProcessor\ObjectStoredFileInfo;
use Bristolian\Service\RoomFileStorage\UploadError;
use Bristolian\UploadedFiles\UploadedFile;

/**
 *
 */
interface RoomFileStorage
{
    /**
     * @param string $user_id
     * @param string $room_id
     * @param UploadedFile $uploadedFile
     * @return string|UploadError - the file_id or an error
     */
    public function storeFileForRoomAndUser(
        string $user_id,
        string $room_id,
        UploadedFile $uploadedFile
    ): string|UploadError;
}
