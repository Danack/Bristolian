<?php

namespace Bristolian\Repo\RoomSourceLinkRepo;

use Bristolian\Parameters\SourceLinkParam;
use RoomSourceLink;

interface RoomSourceLinkRepo
{
    public function addSourceLink(
        string $user_id,
        string $room_id,
        string $file_id,
        SourceLinkParam $sourceLinkParam
    ): string;


    /**
     * @param string $room_id
     * @return RoomSourceLink[]
     */
    public function getSourceLinksForRoom(string $room_id): array;

    /**
     * @param string $room_id
     * @param string $file_id
     * @return RoomSourceLink[]
     */
    public function getSourceLinksForRoomAndFile(
        string $room_id,
        string $file_id,
    ): array;
}
