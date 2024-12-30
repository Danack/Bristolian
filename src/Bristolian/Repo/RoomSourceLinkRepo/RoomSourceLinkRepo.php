<?php

namespace Bristolian\Repo\RoomSourceLinkRepo;

use Bristolian\DataType\SourceLinkParam;

interface RoomSourceLinkRepo
{
    public function addSourceLink(
        string $user_id,
        string $room_id,
        string $file_id,
        SourceLinkParam $sourceLinkParam
    ): string;
}
