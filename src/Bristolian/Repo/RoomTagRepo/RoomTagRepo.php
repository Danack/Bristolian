<?php

declare(strict_types = 1);

namespace Bristolian\Repo\RoomTagRepo;

use Bristolian\Model\Generated\RoomTag;
use Bristolian\Parameters\TagParams;

interface RoomTagRepo
{
    public const MAX_TAGS_PER_ROOM = 50;

    /**
     * @return RoomTag[]
     */
    public function getTagsForRoom(string $room_id): array;

    public function createTag(string $room_id, TagParams $params): RoomTag;
}
