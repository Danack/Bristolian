<?php

declare(strict_types = 1);

namespace Bristolian\Repo\RoomTagRepo;

use Bristolian\Exception\TooManyRoomTagsException;
use Bristolian\Model\Generated\RoomTag;
use Bristolian\Parameters\TagParams;
use Ramsey\Uuid\Uuid;

class FakeRoomTagRepo implements RoomTagRepo
{
    /**
     * @var RoomTag[]
     */
    private array $tags = [];

    public function getTagsForRoom(string $room_id): array
    {
        $result = [];
        foreach ($this->tags as $tag) {
            if ($tag->room_id === $room_id) {
                $result[] = $tag;
            }
        }
        return $result;
    }

    public function createTag(string $room_id, TagParams $params): RoomTag
    {
        $existingTags = $this->getTagsForRoom($room_id);
        if (count($existingTags) >= RoomTagRepo::MAX_TAGS_PER_ROOM) {
            throw TooManyRoomTagsException::forMaxReached(RoomTagRepo::MAX_TAGS_PER_ROOM);
        }

        $tag_id = Uuid::uuid7()->toString();
        $tag = new RoomTag(
            $tag_id,
            $room_id,
            $params->text,
            $params->description,
            new \DateTimeImmutable()
        );
        $this->tags[] = $tag;
        return $tag;
    }
}
