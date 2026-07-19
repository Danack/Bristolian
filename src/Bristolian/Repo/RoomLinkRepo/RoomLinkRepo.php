<?php

namespace Bristolian\Repo\RoomLinkRepo;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use Bristolian\Database\link;
use Bristolian\Database\room_link;
use Bristolian\Database\room_link_tag;
use Bristolian\Model\Generated\RoomLink;
use Bristolian\Model\Types\RoomLinkWithUrl;
use Bristolian\Parameters\LinkParam;
use Bristolian\Parameters\RoomContentSearchParams;

/**
 * Stores and retrieves information about which links are in which rooms.
 */
interface RoomLinkRepo
{
    /**
     * @param string $room_id
     * @return RoomLinkWithUrl[]
     */
    #[ReadsTable(link::class)]
    #[ReadsTable(room_link::class)]
    #[ReadsTable(room_link_tag::class)]
    public function getLinksForRoom(string $room_id, RoomContentSearchParams $search): array;

    #[WritesTable(room_link::class)]
    public function addLinkToRoomFromParam(
        string $user_id,
        string $room_id,
        LinkParam $linkParam
    ): string;

    // TODO - maybe this shouldn't return null.
    #[ReadsTable(room_link::class)]
    public function getRoomLink(string $room_link_id): RoomLink|null;

    /**
     * Update a room link's title and/or description. Null values mean "store NULL".
     *
     * @throws \Bristolian\Exception\ContentNotFoundException if the link is not in the given room
     */
    #[WritesTable(room_link::class)]
    public function updateTitleAndDescription(
        string $room_id,
        string $room_link_id,
        ?string $title,
        ?string $description
    ): void;

//    /**
//     * Get the stored file details for _this_ room. Rooms can have different details
//     * e.g. people might not agree on the proper name of a file
//     *
//     * @param string $room_id
//     * @param string $file_id
//     * @return StoredFile|null
//     */
//    public function getFileDetails(string $room_id, string $file_id): StoredFile|null;
}
