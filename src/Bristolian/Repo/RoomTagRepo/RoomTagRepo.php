<?php

declare(strict_types = 1);

namespace Bristolian\Repo\RoomTagRepo;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use Bristolian\Database\room_tag;
use Bristolian\Model\Generated\RoomTag;
use Bristolian\Parameters\TagParams;

interface RoomTagRepo
{
    public const MAX_TAGS_PER_ROOM = 50;

    /**
     * @return RoomTag[]
     */
    #[ReadsTable(room_tag::class)]
    public function getTagsForRoom(string $room_id): array;

    #[WritesTable(room_tag::class)]
    #[ReadsTable(room_tag::class)]
    public function createTag(string $room_id, TagParams $params): RoomTag;
}
