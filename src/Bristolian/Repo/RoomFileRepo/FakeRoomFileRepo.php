<?php

namespace Bristolian\Repo\RoomFileRepo;

use StoredFile;

class FakeRoomFileRepo implements RoomFileRepo
{
    /**
     * @var array<string, StoredFile>
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
        
        // Create a fake StoredFile if it doesn't exist
        if (!isset($this->files[$fileStorageId])) {
            $this->files[$fileStorageId] = new StoredFile(
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
     * @return StoredFile[]
     */
    public function getFilesForRoom(string $room_id): array
    {
        if (!isset($this->roomFiles[$room_id])) {
            return [];
        }

        $filesForRoom = [];
        foreach ($this->roomFiles[$room_id] as $fileStorageId) {
            if (isset($this->files[$fileStorageId])) {
                $filesForRoom[] = $this->files[$fileStorageId];
            }
        }

        return $filesForRoom;
    }

    public function getFileDetails(string $room_id, string $file_id): StoredFile|null
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
