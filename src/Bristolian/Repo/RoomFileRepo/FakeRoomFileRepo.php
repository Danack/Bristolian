<?php

namespace Bristolian\Repo\RoomFileRepo;

use Bristolian\Model\StoredFile;

class FakeRoomFileRepo implements RoomFileRepo
{
    /**
     * @var array<array{0:string, 1:string}>
     */
    private $filesAndRooms = [];

    public function addFileToRoom(string $fileStorageId, string $room_id): void
    {
        // TODO - change this to store File so they can be returned.
        $this->filesAndRooms[] = [$fileStorageId, $room_id];
    }

    /**
     * @param string $room_id
     * @return StoredFile[]
     */
    public function getFilesForRoom(string $room_id): array
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
