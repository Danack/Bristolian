<?php

namespace Bristolian\Repo\RoomFileRepo;

use Bristolian\Model\StoredFile;

class FakeRoomFileRepo implements RoomFileRepo
{
    private $filesAndRooms = [];

    public function addFileToRoom(string $fileStorageId, string $room_id)
    {
        $this->filesAndRooms[] = [$fileStorageId, $room_id];
    }

    public function getFilesForRoom(string $room_id)
    {
        $filesForRoom = [];

        foreach ($this->filesAndRooms as $fileAndRoom) {
            [$fileStorageId, $file_room_id] = $fileAndRoom;
            if ($room_id === $file_room_id) {
                $filesForRoom[] = $fileStorageId;
            }
        }

        return $filesForRoom;
    }

    public function getFileDetails(string $room_id, string $file_id): StoredFile|null
    {
        // TODO - needs implementing, and probably moving to a separate repo
        return null;
    }
}
