<?php

namespace Bristolian\Repo\RoomSourceLinkRepo;

use Bristolian\DataType\SourceLinkHighlightParam;

interface RoomSourceLinkRepo
{
    public function addSourceLink(
        string $user_id,
        string $room_id,
        string $file_id,
        string $title,
        string $highlights_json
    ): string;
}
