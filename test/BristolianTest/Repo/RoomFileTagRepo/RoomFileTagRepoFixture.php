<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomFileTagRepo;

use Bristolian\Repo\RoomFileTagRepo\RoomFileTagRepo;
use BristolianTest\BaseTestCase;

/**
 * Abstract test class for RoomFileTagRepo implementations.
 *
 */

abstract class RoomFileTagRepoFixture extends BaseTestCase
{
    abstract public function getTestInstance(): RoomFileTagRepo;

    public function test_getTagIdsForRoomFile_returns_empty_initially(): void
    {
        $repo = $this->getTestInstance();
        $ids = $repo->getTagIdsForRoomFile('room-1', 'file-1');
        $this->assertSame([], $ids);
    }

    public function test_setTagsForRoomFile_and_getTagIdsForRoomFile_roundtrip(): void
    {
        $repo = $this->getTestInstance();
        $room_id = 'room-1';
        $file_id = 'file-1';
        $tag_ids = ['tag-a', 'tag-b'];
        $repo->setTagsForRoomFile($room_id, $file_id, $tag_ids);
        $this->assertEquals($tag_ids, $repo->getTagIdsForRoomFile($room_id, $file_id));
    }

    public function test_setTagsForRoomFile_replaces_existing(): void
    {
        $repo = $this->getTestInstance();
        $room_id = 'room-1';
        $file_id = 'file-1';
        $repo->setTagsForRoomFile($room_id, $file_id, ['tag-1']);
        $repo->setTagsForRoomFile($room_id, $file_id, ['tag-2', 'tag-3']);
        $this->assertEquals(['tag-2', 'tag-3'], $repo->getTagIdsForRoomFile($room_id, $file_id));
    }
}
