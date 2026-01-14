<?php

namespace Bristolian\Repo\RoomFileRepo;

use Bristolian\Model\Generated\RoomFileObjectInfo;

/**
 * Stores and retrieves information about which files are in which rooms.
 */
interface RoomFileRepo
{
    public function addFileToRoom(string $fileStorageId, string $room_id): void;

    /**
     * @param string $room_id
     * @return RoomFileObjectInfo[]
     */
    public function getFilesForRoom(string $room_id);

    /**
     * Get the stored file details for _this_ room. Rooms can have different details
     * e.g. people might not agree on the proper name of a file
     *
     * @param string $room_id
     * @param string $file_id
     * @return RoomFileObjectInfo|null
     */
    public function getFileDetails(string $room_id, string $file_id): RoomFileObjectInfo|null;
}
