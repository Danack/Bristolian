<?php

declare(strict_types = 1);

namespace Bristolian\Repo\RoomTagRepo;

use Bristolian\Database\room_tag;
use Bristolian\Exception\TooManyRoomTagsException;
use Bristolian\Model\Generated\RoomTag;
use Bristolian\Parameters\TagParams;
use Bristolian\PdoSimple\PdoSimple;
use Ramsey\Uuid\Uuid;
use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;

class PdoRoomTagRepo implements RoomTagRepo
{
    public function __construct(private PdoSimple $pdo_simple)
    {
    }

    #[WritesTable(room_tag::class)]
    #[ReadsTable(room_tag::class)]
    public function createTag(string $room_id, TagParams $params): RoomTag
    {
        $existingTags = $this->getTagsForRoom($room_id);
        if (count($existingTags) >= RoomTagRepo::MAX_TAGS_PER_ROOM) {
            throw TooManyRoomTagsException::forMaxReached(RoomTagRepo::MAX_TAGS_PER_ROOM);
        }

        $tag_id = Uuid::uuid7()->toString();
        $this->pdo_simple->insert(room_tag::INSERT, [
            ':room_id' => $room_id,
            ':tag_id' => $tag_id,
            ':description' => $params->description,
            ':text' => $params->text,
        ]);

        return $this->pdo_simple->fetchOneAsObjectConstructor(
            room_tag::SELECT . " where tag_id = :tag_id",
            [':tag_id' => $tag_id],
            RoomTag::class
        );
    }

    /**
     * @return RoomTag[]
     */
    #[ReadsTable(room_tag::class)]
    public function getTagsForRoom(string $room_id): array
    {
        return $this->pdo_simple->fetchAllAsObjectConstructor(
            room_tag::SELECT . " where room_id = :room_id",
            [':room_id' => $room_id],
            RoomTag::class
        );
    }
}
