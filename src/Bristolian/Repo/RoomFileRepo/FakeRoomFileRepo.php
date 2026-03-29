<?php

namespace Bristolian\Repo\RoomFileRepo;

use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Model\Generated\RoomFileObjectInfo;
use Bristolian\Model\Types\RoomFileInRoom;
use Bristolian\Parameters\RoomContentSearchParams;

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

    /**
     * document_timestamp per (room_id, fileStorageId) for testing filter behaviour.
     * @var array<string, array<string, \DateTimeInterface>>
     */
    private array $documentTimestamps = [];

    /**
     * @var array<string, array<string, string|null>>
     */
    private array $roomFileDescriptions = [];

    /**
     * @var array<string, array<string, string|null>>
     */
    private array $roomFileNotes = [];

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
    public function getFilesForRoom(string $room_id, RoomContentSearchParams $search): array
    {
        if (!isset($this->roomFiles[$room_id])) {
            return [];
        }

        $filesForRoom = [];
        foreach ($this->roomFiles[$room_id] as $fileStorageId) {
            if (isset($this->files[$fileStorageId])) {
                $file = $this->files[$fileStorageId];
                $documentTimestamp = $this->documentTimestamps[$room_id][$fileStorageId] ?? null;
                $description = $this->roomFileDescriptions[$room_id][$fileStorageId] ?? null;
                $note = $this->roomFileNotes[$room_id][$fileStorageId] ?? null;
                $filesForRoom[] = new RoomFileInRoom(
                    $file->id,
                    $file->normalized_name,
                    $file->original_filename,
                    $file->state,
                    $file->size,
                    $file->user_id,
                    $file->created_at,
                    $documentTimestamp,
                    $description,
                    $note
                );
            }
        }

        $filesForRoom = $this->filterFilesBySearch($filesForRoom, $search);
        usort($filesForRoom, fn ($a, $b) => $b->created_at <=> $a->created_at);
        return array_slice($filesForRoom, 0, $search->getLimit());
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

    /**
     * Register stored file metadata before addFileToRoom so tests can control original_filename.
     *
     * @internal
     */
    public function registerFileObjectInfo(RoomFileObjectInfo $info): void
    {
        $this->files[$info->id] = $info;
    }

    /**
     * @return RoomFileInRoom[]
     */
    public function getFilesInRoomByOriginalFilename(string $room_id, string $original_filename): array
    {
        if (!isset($this->roomFiles[$room_id])) {
            return [];
        }

        $matching = [];
        foreach ($this->roomFiles[$room_id] as $fileStorageId) {
            if (!isset($this->files[$fileStorageId])) {
                continue;
            }
            $file = $this->files[$fileStorageId];
            if ($file->original_filename !== $original_filename) {
                continue;
            }
            $documentTimestamp = $this->documentTimestamps[$room_id][$fileStorageId] ?? null;
            $description = $this->roomFileDescriptions[$room_id][$fileStorageId] ?? null;
            $note = $this->roomFileNotes[$room_id][$fileStorageId] ?? null;
            $matching[] = new RoomFileInRoom(
                $file->id,
                $file->normalized_name,
                $file->original_filename,
                $file->state,
                $file->size,
                $file->user_id,
                $file->created_at,
                $documentTimestamp,
                $description,
                $note
            );
        }

        usort($matching, fn (RoomFileInRoom $a, RoomFileInRoom $b) => $b->created_at <=> $a->created_at);

        return $matching;
    }

    /**
     * Set document_timestamp for a file in a room (for testing filter behaviour).
     * Call after addFileToRoom; the file must already be in the room.
     */
    public function setDocumentTimestampForFileInRoom(string $room_id, string $fileStorageId, \DateTimeInterface $documentTimestamp): void
    {
        if (!isset($this->documentTimestamps[$room_id])) {
            $this->documentTimestamps[$room_id] = [];
        }
        $this->documentTimestamps[$room_id][$fileStorageId] = $documentTimestamp;
    }

    public function updateRoomFileDetails(
        string $room_id,
        string $stored_file_id,
        ?string $description,
        ?string $note,
        ?\DateTimeInterface $document_timestamp
    ): void {
        if (!isset($this->roomFiles[$room_id]) || !in_array($stored_file_id, $this->roomFiles[$room_id], true)) {
            throw new ContentNotFoundException('File not found in room');
        }

        if (!isset($this->roomFileDescriptions[$room_id])) {
            $this->roomFileDescriptions[$room_id] = [];
        }
        if (!isset($this->roomFileNotes[$room_id])) {
            $this->roomFileNotes[$room_id] = [];
        }
        $this->roomFileDescriptions[$room_id][$stored_file_id] = $description;
        $this->roomFileNotes[$room_id][$stored_file_id] = $note;

        if (!isset($this->documentTimestamps[$room_id])) {
            $this->documentTimestamps[$room_id] = [];
        }
        $this->documentTimestamps[$room_id][$stored_file_id] = $document_timestamp;
    }

    /**
     * @param RoomFileInRoom[] $files
     * @return RoomFileInRoom[]
     */
    private function filterFilesBySearch(array $files, RoomContentSearchParams $search): array
    {
        return array_filter($files, function (RoomFileInRoom $file) use ($search): bool {
            if ($search->title !== null && $search->title !== '' && stripos($file->original_filename, $search->title) === false) {
                return false;
            }
            if ($search->created_at_after !== null && $file->created_at < $search->created_at_after) {
                return false;
            }
            if ($search->created_at_before !== null && $file->created_at > $search->created_at_before) {
                return false;
            }
            if ($search->document_timestamp_after !== null && $file->document_timestamp !== null && $file->document_timestamp < $search->document_timestamp_after) {
                return false;
            }
            if ($search->document_timestamp_before !== null && $file->document_timestamp !== null && $file->document_timestamp > $search->document_timestamp_before) {
                return false;
            }
            return true;
        });
    }
}
