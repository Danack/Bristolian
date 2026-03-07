<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomVideoTagRepo;

use Bristolian\Repo\RoomVideoTagRepo\InMemoryRoomVideoTagRepo;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Bristolian\Repo\RoomVideoTagRepo\InMemoryRoomVideoTagRepo
 * @group standard_repo
 */
class InMemoryRoomVideoTagRepoTest extends TestCase
{
    public function test_getTagIds_returns_empty_initially(): void
    {
        $repo = new InMemoryRoomVideoTagRepo();

        $tagIds = $repo->getTagIdsForRoomVideo('room-video-1');

        $this->assertSame([], $tagIds);
    }

    public function test_setTags_and_getTagIds_roundtrip(): void
    {
        $repo = new InMemoryRoomVideoTagRepo();

        $repo->setTagsForRoomVideo('room-video-1', ['tag-a', 'tag-b']);

        $this->assertSame(['tag-a', 'tag-b'], $repo->getTagIdsForRoomVideo('room-video-1'));
    }

    public function test_setTags_replaces_previous_tags(): void
    {
        $repo = new InMemoryRoomVideoTagRepo();

        $repo->setTagsForRoomVideo('room-video-1', ['tag-a', 'tag-b']);
        $repo->setTagsForRoomVideo('room-video-1', ['tag-c']);

        $this->assertSame(['tag-c'], $repo->getTagIdsForRoomVideo('room-video-1'));
    }

    public function test_setTags_empty_array_clears_tags(): void
    {
        $repo = new InMemoryRoomVideoTagRepo();

        $repo->setTagsForRoomVideo('room-video-1', ['tag-a', 'tag-b']);
        $repo->setTagsForRoomVideo('room-video-1', []);

        $this->assertSame([], $repo->getTagIdsForRoomVideo('room-video-1'));
    }

    public function test_different_room_videos_have_independent_tags(): void
    {
        $repo = new InMemoryRoomVideoTagRepo();

        $repo->setTagsForRoomVideo('room-video-1', ['tag-a']);
        $repo->setTagsForRoomVideo('room-video-2', ['tag-b', 'tag-c']);

        $this->assertSame(['tag-a'], $repo->getTagIdsForRoomVideo('room-video-1'));
        $this->assertSame(['tag-b', 'tag-c'], $repo->getTagIdsForRoomVideo('room-video-2'));
    }
}
