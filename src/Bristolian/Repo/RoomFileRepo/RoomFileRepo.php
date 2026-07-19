<?php

namespace Bristolian\Repo\RoomFileRepo;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use Bristolian\Database\room_file;
use Bristolian\Database\room_file_object_info;
use Bristolian\Database\room_file_tag;
use Bristolian\Model\Generated\RoomFileObjectInfo;
use Bristolian\Model\Types\RoomFileInRoom;
use Bristolian\Parameters\RoomContentSearchParams;

/**
 * Stores and retrieves information about which files are in which rooms.
 */
interface RoomFileRepo
{
    #[WritesTable(room_file::class)]
    public function addFileToRoom(string $fileStorageId, string $room_id): void;

    /**
     * @param string $room_id
     * @return RoomFileInRoom[]
     */
    #[ReadsTable(room_file::class)]
    #[ReadsTable(room_file_object_info::class)]
    #[ReadsTable(room_file_tag::class)]
    public function getFilesForRoom(string $room_id, RoomContentSearchParams $search): array;

    /**
     * Get the stored file details for _this_ room. Rooms can have different details
     * e.g. people might not agree on the proper name of a file
     *
     * @param string $room_id
     * @param string $file_id
     * @return RoomFileObjectInfo|null
     */
    #[ReadsTable(room_file_object_info::class)]
    #[ReadsTable(room_file::class)]
    public function getFileDetails(string $room_id, string $file_id): RoomFileObjectInfo|null;

    /**
     * Files in the room whose stored original filename equals $original_filename (exact match).
     *
     * @return RoomFileInRoom[]
     */
    #[ReadsTable(room_file_object_info::class)]
    #[ReadsTable(room_file::class)]
    public function getFilesInRoomByOriginalFilename(string $room_id, string $original_filename): array;

    /**
     * Update room-specific metadata for a file. {@see room_file} row must exist for (room_id, stored_file_id).
     *
     * @throws \Bristolian\Exception\ContentNotFoundException when no row is updated
     */
    #[WritesTable(room_file::class)]
    public function updateRoomFileDetails(
        string $room_id,
        string $stored_file_id,
        ?string $description,
        ?string $note,
        ?\DateTimeInterface $document_timestamp
    ): void;
}
