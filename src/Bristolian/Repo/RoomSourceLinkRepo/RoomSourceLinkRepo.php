<?php

namespace Bristolian\Repo\RoomSourceLinkRepo;

use Bristolian\Model\Types\RoomSourceLinkView;
use Bristolian\Parameters\SourceLinkParam;

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
     * @return RoomSourceLinkView[]
     */
    public function getSourceLinksForRoom(string $room_id): array;

    /**
     * @param string $room_id
     * @param string $file_id
     * @return RoomSourceLinkView[]
     */
    public function getSourceLinksForRoomAndFile(
        string $room_id,
        string $file_id,
    ): array;
}
