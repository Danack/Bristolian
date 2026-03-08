<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomVideoRepo;

use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Model\Generated\RoomVideo;
use Bristolian\Model\Types\RoomVideoWithTags;
use Bristolian\Parameters\TagParams;
use Bristolian\Repo\RoomTagRepo\FakeRoomTagRepo;
use Bristolian\Repo\RoomVideoRepo\InMemoryRoomVideoRepo;
use Bristolian\Repo\RoomVideoTagRepo\InMemoryRoomVideoTagRepo;
use Bristolian\Repo\VideoRepo\InMemoryVideoRepo;
use PHPUnit\Framework\TestCase;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Repo\RoomVideoRepo\InMemoryRoomVideoRepo
 * @group standard_repo
 */
class InMemoryRoomVideoRepoTest extends TestCase
{
    private InMemoryRoomVideoTagRepo $roomVideoTagRepo;
    private InMemoryVideoRepo $videoRepo;
    private FakeRoomTagRepo $roomTagRepo;
    private InMemoryRoomVideoRepo $repo;

    protected function setUp(): void
    {
        $this->roomVideoTagRepo = new InMemoryRoomVideoTagRepo();
        $this->videoRepo = new InMemoryVideoRepo();
        $this->roomTagRepo = new FakeRoomTagRepo();
        $this->repo = new InMemoryRoomVideoRepo(
            $this->roomVideoTagRepo,
            $this->videoRepo,
            $this->roomTagRepo,
        );
    }

    public function test_getVideosForRoom_returns_empty_initially(): void
    {
        $videos = $this->repo->getVideosForRoom('room-1');

        $this->assertSame([], $videos);
    }

    public function test_addVideo_returns_room_video(): void
    {
        $videoId = $this->videoRepo->create('user-1', 'dQw4w9WgXcQ');

        $roomVideo = $this->repo->addVideo('room-1', $videoId, 'My Video', 'A description');

        $this->assertInstanceOf(RoomVideo::class, $roomVideo);
        $this->assertNotEmpty($roomVideo->id);
        $this->assertSame('room-1', $roomVideo->room_id);
        $this->assertSame($videoId, $roomVideo->video_id);
        $this->assertSame('My Video', $roomVideo->title);
        $this->assertSame('A description', $roomVideo->description);
        $this->assertNull($roomVideo->start_seconds);
        $this->assertNull($roomVideo->end_seconds);
        $this->assertInstanceOf(\DateTimeInterface::class, $roomVideo->created_at);
    }

    public function test_addVideo_with_null_title_and_description(): void
    {
        $videoId = $this->videoRepo->create('user-1', 'dQw4w9WgXcQ');

        $roomVideo = $this->repo->addVideo('room-1', $videoId);

        $this->assertNull($roomVideo->title);
        $this->assertNull($roomVideo->description);
    }

    public function test_addClip_returns_room_video_with_times(): void
    {
        $videoId = $this->videoRepo->create('user-1', 'dQw4w9WgXcQ');

        $roomVideo = $this->repo->addClip('room-1', $videoId, 'Clip Title', 'Clip Desc', 30, 90);

        $this->assertInstanceOf(RoomVideo::class, $roomVideo);
        $this->assertSame('room-1', $roomVideo->room_id);
        $this->assertSame($videoId, $roomVideo->video_id);
        $this->assertSame('Clip Title', $roomVideo->title);
        $this->assertSame('Clip Desc', $roomVideo->description);
        $this->assertSame(30, $roomVideo->start_seconds);
        $this->assertSame(90, $roomVideo->end_seconds);
    }

    public function test_getVideosForRoom_returns_added_videos(): void
    {
        $videoId = $this->videoRepo->create('user-1', 'dQw4w9WgXcQ');

        $this->repo->addVideo('room-1', $videoId, 'Video 1');
        $this->repo->addVideo('room-1', $videoId, 'Video 2');

        $videos = $this->repo->getVideosForRoom('room-1');

        $this->assertCount(2, $videos);
        $this->assertContainsOnlyInstancesOf(RoomVideo::class, $videos);
    }

    public function test_getVideosForRoom_different_rooms_are_independent(): void
    {
        $videoId = $this->videoRepo->create('user-1', 'dQw4w9WgXcQ');

        $this->repo->addVideo('room-1', $videoId, 'Room 1 Video');
        $this->repo->addVideo('room-2', $videoId, 'Room 2 Video');

        $room1Videos = $this->repo->getVideosForRoom('room-1');
        $room2Videos = $this->repo->getVideosForRoom('room-2');

        $this->assertCount(1, $room1Videos);
        $this->assertCount(1, $room2Videos);
        $this->assertSame('Room 1 Video', $room1Videos[0]->title);
        $this->assertSame('Room 2 Video', $room2Videos[0]->title);
    }

    public function test_getRoomVideo_returns_video_by_id(): void
    {
        $videoId = $this->videoRepo->create('user-1', 'dQw4w9WgXcQ');
        $roomVideo = $this->repo->addVideo('room-1', $videoId, 'Test Video');

        $fetched = $this->repo->getRoomVideo($roomVideo->id);

        $this->assertSame($roomVideo->id, $fetched->id);
        $this->assertSame('Test Video', $fetched->title);
    }

    public function test_getRoomVideo_throws_for_nonexistent_id(): void
    {
        $this->expectException(ContentNotFoundException::class);
        $this->repo->getRoomVideo('nonexistent-id');
    }

    public function test_getRoomVideoForRoom_returns_video_in_correct_room(): void
    {
        $videoId = $this->videoRepo->create('user-1', 'dQw4w9WgXcQ');
        $roomVideo = $this->repo->addVideo('room-1', $videoId, 'Test Video');

        $fetched = $this->repo->getRoomVideoForRoom('room-1', $roomVideo->id);

        $this->assertSame($roomVideo->id, $fetched->id);
    }

    public function test_getRoomVideoForRoom_throws_for_wrong_room(): void
    {
        $videoId = $this->videoRepo->create('user-1', 'dQw4w9WgXcQ');
        $roomVideo = $this->repo->addVideo('room-1', $videoId, 'Test Video');

        $this->expectException(ContentNotFoundException::class);
        $this->repo->getRoomVideoForRoom('room-2', $roomVideo->id);
    }

    public function test_getRoomVideoForRoom_throws_for_nonexistent_id(): void
    {
        $this->expectException(ContentNotFoundException::class);
        $this->repo->getRoomVideoForRoom('room-1', 'nonexistent-id');
    }

    public function test_getVideosForRoomWithTags_returns_empty_initially(): void
    {
        $withTags = $this->repo->getVideosForRoomWithTags('room-1');

        $this->assertSame([], $withTags);
    }

    public function test_getVideosForRoomWithTags_returns_video_with_youtube_id(): void
    {
        $videoId = $this->videoRepo->create('user-1', 'dQw4w9WgXcQ');
        $this->repo->addVideo('room-1', $videoId, 'My Video');

        $withTags = $this->repo->getVideosForRoomWithTags('room-1');

        $this->assertCount(1, $withTags);
        $this->assertInstanceOf(RoomVideoWithTags::class, $withTags[0]);
        $this->assertSame('dQw4w9WgXcQ', $withTags[0]->youtube_video_id);
        $this->assertSame('My Video', $withTags[0]->title);
        $this->assertSame([], $withTags[0]->tags);
    }

    public function test_getVideosForRoomWithTags_includes_tags(): void
    {
        $videoId = $this->videoRepo->create('user-1', 'dQw4w9WgXcQ');
        $roomVideo = $this->repo->addVideo('room-1', $videoId, 'Tagged Video');

        $tagParams = TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'interesting',
            'description' => 'Interesting content',
        ]));
        $roomTag = $this->roomTagRepo->createTag('room-1', $tagParams);
        $this->roomVideoTagRepo->setTagsForRoomVideo($roomVideo->id, [$roomTag->tag_id]);

        $withTags = $this->repo->getVideosForRoomWithTags('room-1');

        $this->assertCount(1, $withTags);
        $this->assertCount(1, $withTags[0]->tags);
        $this->assertSame('interesting', $withTags[0]->tags[0]->text);
    }

    public function test_getVideosForRoomWithTags_skips_unknown_tag_ids(): void
    {
        $videoId = $this->videoRepo->create('user-1', 'dQw4w9WgXcQ');
        $roomVideo = $this->repo->addVideo('room-1', $videoId, 'Video');

        $this->roomVideoTagRepo->setTagsForRoomVideo($roomVideo->id, ['nonexistent-tag-id']);

        $withTags = $this->repo->getVideosForRoomWithTags('room-1');

        $this->assertCount(1, $withTags);
        $this->assertSame([], $withTags[0]->tags);
    }

    public function test_updateTitleAndDescription_updates_title_and_description(): void
    {
        $videoId = $this->videoRepo->create('user-1', 'dQw4w9WgXcQ');
        $roomVideo = $this->repo->addVideo('room-1', $videoId, 'Original Title', 'Original description');

        $this->repo->updateTitleAndDescription('room-1', $roomVideo->id, 'New Title', 'New description');

        $fetched = $this->repo->getRoomVideo($roomVideo->id);
        $this->assertSame('New Title', $fetched->title);
        $this->assertSame('New description', $fetched->description);
        $this->assertSame($roomVideo->id, $fetched->id);
        $this->assertSame($roomVideo->created_at->getTimestamp(), $fetched->created_at->getTimestamp());
    }

    public function test_updateTitleAndDescription_leaves_unchanged_when_null(): void
    {
        $videoId = $this->videoRepo->create('user-1', 'dQw4w9WgXcQ');
        $roomVideo = $this->repo->addVideo('room-1', $videoId, 'Keep Title', 'Keep description');

        $this->repo->updateTitleAndDescription('room-1', $roomVideo->id, null, null);

        $fetched = $this->repo->getRoomVideo($roomVideo->id);
        $this->assertSame('Keep Title', $fetched->title);
        $this->assertSame('Keep description', $fetched->description);
    }

    public function test_updateTitleAndDescription_updates_only_title_when_description_null(): void
    {
        $videoId = $this->videoRepo->create('user-1', 'dQw4w9WgXcQ');
        $roomVideo = $this->repo->addVideo('room-1', $videoId, 'Old', 'Keep this');

        $this->repo->updateTitleAndDescription('room-1', $roomVideo->id, 'New Title', null);

        $fetched = $this->repo->getRoomVideo($roomVideo->id);
        $this->assertSame('New Title', $fetched->title);
        $this->assertSame('Keep this', $fetched->description);
    }

    public function test_updateTitleAndDescription_updates_only_description_when_title_null(): void
    {
        $videoId = $this->videoRepo->create('user-1', 'dQw4w9WgXcQ');
        $roomVideo = $this->repo->addVideo('room-1', $videoId, 'Keep this', 'Old desc');

        $this->repo->updateTitleAndDescription('room-1', $roomVideo->id, null, 'New description');

        $fetched = $this->repo->getRoomVideo($roomVideo->id);
        $this->assertSame('Keep this', $fetched->title);
        $this->assertSame('New description', $fetched->description);
    }

    public function test_updateTitleAndDescription_throws_for_wrong_room(): void
    {
        $videoId = $this->videoRepo->create('user-1', 'dQw4w9WgXcQ');
        $roomVideo = $this->repo->addVideo('room-1', $videoId, 'Video', null);

        $this->expectException(ContentNotFoundException::class);
        $this->repo->updateTitleAndDescription('room-2', $roomVideo->id, 'Hacked', null);
    }

    public function test_updateTitleAndDescription_throws_for_nonexistent_id(): void
    {
        $this->expectException(ContentNotFoundException::class);
        $this->repo->updateTitleAndDescription('room-1', 'nonexistent-id', 'Title', null);
    }
}
