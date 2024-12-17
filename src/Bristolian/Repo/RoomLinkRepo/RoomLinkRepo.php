<?php

namespace Bristolian\Repo\RoomLinkRepo;

use Bristolian\Model\RoomLink;
use Bristolian\Model\StoredFile;
use Bristolian\DataType\LinkParam;

/**
 * Stores and retrieves information about which links are in which rooms.
 */
interface RoomLinkRepo
{
//    public function addLinkToRoom(string $link_id, string $room_id);

    public function getLinksForRoom(string $room_id);

    public function addLinkToRoomFromParam(
        string $user_id,
        string $room_id,
        LinkParam $linkParam
    ): string;

    // TODO - maybe this shouldn't return null.
    public function getRoomLink(string $room_link_id): RoomLink|null;

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
