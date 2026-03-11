<?php

namespace Bristolian\Repo\RoomFileRepo;

use Bristolian\Model\Generated\RoomFileObjectInfo;
use Bristolian\Model\Types\RoomFileInRoom;

class FakeRoomFileRepo implements RoomFileRepo
{
    /**
     * @var array<string, RoomFileObjectInfo>
     */
    private array $files = [];

    /**
     * @var array<string, array<string>>
     */
    private array $roomFiles = [];

    public function addFileToRoom(string $fileStorageId, string $room_id): void
    {
        if (!isset($this->roomFiles[$room_id])) {
            $this->roomFiles[$room_id] = [];
        }
        
        $this->roomFiles[$room_id][] = $fileStorageId;
        
        // Create a fake RoomFileObjectInfo if it doesn't exist
        if (!isset($this->files[$fileStorageId])) {
            $this->files[$fileStorageId] = new RoomFileObjectInfo(
                id: $fileStorageId,
                normalized_name: 'normalized_' . $fileStorageId . '.txt',
                original_filename: 'original_' . $fileStorageId . '.txt',
                state: 'uploaded',
                size: 1024,
                user_id: 'fake_user_id',
                created_at: new \DateTimeImmutable()
            );
        }
    }

    /**
     * @param string $room_id
     * @return RoomFileInRoom[]
     */
    public function getFilesForRoom(string $room_id): array
    {
        if (!isset($this->roomFiles[$room_id])) {
            return [];
        }

        $filesForRoom = [];
        foreach ($this->roomFiles[$room_id] as $fileStorageId) {
            if (isset($this->files[$fileStorageId])) {
                $file = $this->files[$fileStorageId];
                $filesForRoom[] = new RoomFileInRoom(
                    $file->id,
                    $file->normalized_name,
                    $file->original_filename,
                    $file->state,
                    $file->size,
                    $file->user_id,
                    $file->created_at,
                    null
                );
            }
        }

        return $filesForRoom;
    }

    public function getFileDetails(string $room_id, string $file_id): RoomFileObjectInfo|null
    {
        if (!isset($this->roomFiles[$room_id])) {
            return null;
        }

        // Check if file is in this room
        if (!in_array($file_id, $this->roomFiles[$room_id], true)) {
            return null;
        }

        return $this->files[$file_id] ?? null;
    }
}
