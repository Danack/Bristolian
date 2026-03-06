<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomVideoRepo;

use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Model\Generated\RoomTag;
use Bristolian\Model\Generated\RoomVideo;
use Bristolian\Model\Generated\Video;
use Bristolian\Model\Types\RoomVideoWithTags;
use Bristolian\Parameters\TagParams;
use Bristolian\Repo\RoomTagRepo\PdoRoomTagRepo;
use Bristolian\Repo\RoomVideoRepo\PdoRoomVideoRepo;
use Bristolian\Repo\RoomVideoRepo\RoomVideoRepo;
use Bristolian\Repo\RoomVideoTagRepo\PdoRoomVideoTagRepo;
use Bristolian\Repo\VideoRepo\PdoVideoRepo;
use BristolianTest\BaseTestCase;
use BristolianTest\Support\HasTestWorld;
use VarMap\ArrayVarMap;

/**
 * @group db
 * @coversNothing
 */
class PdoRoomVideoRepoTest extends BaseTestCase
{
    use HasTestWorld;

    private ?string $roomId = null;
    private ?string $videoId = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->ensureStandardSetup();
        $userId = $this->standardTestData()->getTestingUserId();
        $room = $this->world()->roomRepo()->createRoom(
            $userId,
            'V' . time() . '_' . random_int(100, 999),
            'Room video tests'
        );
        $this->roomId = $room->id;
        $videoRepo = $this->injector->make(PdoVideoRepo::class);
        $this->videoId = $videoRepo->create($userId, 'dQw4w9WgXcQ');
    }

    private function getRepo(): RoomVideoRepo
    {
        return $this->injector->make(PdoRoomVideoRepo::class);
    }

    /**
     * @covers \Bristolian\Repo\RoomVideoRepo\PdoRoomVideoRepo::__construct
     * @covers \Bristolian\Repo\RoomVideoRepo\PdoRoomVideoRepo::getVideosForRoom
     */
    public function test_getVideosForRoom_returns_empty_then_created_videos(): void
    {
        $repo = $this->getRepo();
        assert($this->roomId !== null);

        $videos = $repo->getVideosForRoom($this->roomId);
        $this->assertSame([], $videos);

        $repo->addVideo($this->roomId, $this->videoId, 'Title', 'Description');
        $videos = $repo->getVideosForRoom($this->roomId);
        $this->assertCount(1, $videos);
        $this->assertInstanceOf(RoomVideo::class, $videos[0]);
        $this->assertSame('Title', $videos[0]->title);
        $this->assertSame($this->roomId, $videos[0]->room_id);
    }

    /**
     * @covers \Bristolian\Repo\RoomVideoRepo\PdoRoomVideoRepo::addVideo
     * @covers \Bristolian\Repo\RoomVideoRepo\PdoRoomVideoRepo::getRoomVideo
     */
    public function test_addVideo_and_getRoomVideo(): void
    {
        $repo = $this->getRepo();
        assert($this->roomId !== null && $this->videoId !== null);

        $roomVideo = $repo->addVideo($this->roomId, $this->videoId, 'My Video', null);

        $this->assertInstanceOf(RoomVideo::class, $roomVideo);
        $this->assertSame('My Video', $roomVideo->title);
        $fetched = $repo->getRoomVideo($roomVideo->id);
        $this->assertSame($roomVideo->id, $fetched->id);
    }

    /**
     * @covers \Bristolian\Repo\RoomVideoRepo\PdoRoomVideoRepo::getRoomVideo
     */
    public function test_getRoomVideo_throws_for_nonexistent_id(): void
    {
        $repo = $this->getRepo();

        $this->expectException(ContentNotFoundException::class);
        $this->expectExceptionMessage('not found');

        $repo->getRoomVideo('00000000-0000-0000-0000-000000000000');
    }

    /**
     * @covers \Bristolian\Repo\RoomVideoRepo\PdoRoomVideoRepo::getRoomVideoForRoom
     */
    public function test_getRoomVideoForRoom_returns_video_when_in_room(): void
    {
        $repo = $this->getRepo();
        assert($this->roomId !== null && $this->videoId !== null);

        $roomVideo = $repo->addVideo($this->roomId, $this->videoId, 'In Room', null);
        $fetched = $repo->getRoomVideoForRoom($this->roomId, $roomVideo->id);

        $this->assertSame($roomVideo->id, $fetched->id);
    }

    /**
     * @covers \Bristolian\Repo\RoomVideoRepo\PdoRoomVideoRepo::getRoomVideoForRoom
     */
    public function test_getRoomVideoForRoom_throws_when_video_in_different_room(): void
    {
        $repo = $this->getRepo();
        assert($this->roomId !== null && $this->videoId !== null);

        $roomVideo = $repo->addVideo($this->roomId, $this->videoId, 'In Room', null);

        $otherRoom = $this->world()->roomRepo()->createRoom(
            $this->standardTestData()->getTestingUserId(),
            'O' . time() . '_' . random_int(1000, 9999),
            'Other'
        );

        $this->expectException(ContentNotFoundException::class);
        $this->expectExceptionMessage('not found');

        $repo->getRoomVideoForRoom($otherRoom->id, $roomVideo->id);
    }

    /**
     * @covers \Bristolian\Repo\RoomVideoRepo\PdoRoomVideoRepo::getVideosForRoomWithTags
     */
    public function test_getVideosForRoomWithTags_returns_videos_with_tags_structure(): void
    {
        $repo = $this->getRepo();
        assert($this->roomId !== null && $this->videoId !== null);

        $repo->addVideo($this->roomId, $this->videoId, 'With Tags', 'Desc');

        $withTags = $repo->getVideosForRoomWithTags($this->roomId);
        $this->assertCount(1, $withTags);
        $this->assertInstanceOf(RoomVideoWithTags::class, $withTags[0]);
        $this->assertSame('With Tags', $withTags[0]->title);
    }

    /**
     * getVideosForRoomWithTags with room tags and room-video tags: covers fetchTagIdsForRoomVideo,
     * resolveTagIdsToTags, and roomTagsById build.
     *
     * @covers \Bristolian\Repo\RoomVideoRepo\PdoRoomVideoRepo::getVideosForRoomWithTags
     */
    public function test_getVideosForRoomWithTags_includes_room_video_tags(): void
    {
        $repo = $this->getRepo();
        assert($this->roomId !== null && $this->videoId !== null);

        $roomVideo = $repo->addVideo($this->roomId, $this->videoId, 'Tagged Video', 'Desc');

        $roomTagRepo = $this->injector->make(PdoRoomTagRepo::class);
        $tag = $roomTagRepo->createTag($this->roomId, TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'room-video-tag-' . create_test_uniqid(),
            'description' => 'Tag for room video',
        ])));

        $roomVideoTagRepo = $this->injector->make(PdoRoomVideoTagRepo::class);
        $roomVideoTagRepo->setTagsForRoomVideo($roomVideo->id, [$tag->tag_id]);

        $withTags = $repo->getVideosForRoomWithTags($this->roomId);
        $this->assertCount(1, $withTags);
        $this->assertCount(1, $withTags[0]->tags);
        $this->assertInstanceOf(RoomTag::class, $withTags[0]->tags[0]);
        $this->assertSame($tag->tag_id, $withTags[0]->tags[0]->tag_id);
    }

    /**
     * @covers \Bristolian\Repo\RoomVideoRepo\PdoRoomVideoRepo::addClip
     */
    public function test_addClip_creates_clip_with_start_end_seconds(): void
    {
        $repo = $this->getRepo();
        assert($this->roomId !== null && $this->videoId !== null);

        $clip = $repo->addClip(
            $this->roomId,
            $this->videoId,
            'Clip Title',
            'Clip description',
            10,
            70
        );

        $this->assertInstanceOf(RoomVideo::class, $clip);
        $this->assertSame('Clip Title', $clip->title);
        $this->assertSame(10, $clip->start_seconds);
        $this->assertSame(70, $clip->end_seconds);
    }

    /**
     * @covers \Bristolian\Repo\RoomVideoRepo\PdoRoomVideoRepo::fetchTagIdsForRoomVideo
     */
    public function test_fetchTagIdsForRoomVideo_returns_empty_when_no_tags(): void
    {
        $repo = $this->injector->make(PdoRoomVideoRepo::class);
        assert($this->roomId !== null && $this->videoId !== null);

        $roomVideo = $repo->addVideo($this->roomId, $this->videoId, 'No Tags', null);

        $tagIds = $repo->fetchTagIdsForRoomVideo($roomVideo->id);
        $this->assertSame([], $tagIds);
    }

    /**
     * @covers \Bristolian\Repo\RoomVideoRepo\PdoRoomVideoRepo::fetchTagIdsForRoomVideo
     */
    public function test_fetchTagIdsForRoomVideo_returns_tag_ids_when_tags_set(): void
    {
        $repo = $this->injector->make(PdoRoomVideoRepo::class);
        assert($this->roomId !== null && $this->videoId !== null);

        $roomVideo = $repo->addVideo($this->roomId, $this->videoId, 'Tagged', null);
        $roomTagRepo = $this->injector->make(PdoRoomTagRepo::class);
        $tag = $roomTagRepo->createTag($this->roomId, TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'fetch-tag-' . create_test_uniqid(),
            'description' => 'Desc',
        ])));
        $roomVideoTagRepo = $this->injector->make(PdoRoomVideoTagRepo::class);
        $roomVideoTagRepo->setTagsForRoomVideo($roomVideo->id, [$tag->tag_id]);

        $tagIds = $repo->fetchTagIdsForRoomVideo($roomVideo->id);
        $this->assertSame([$tag->tag_id], $tagIds);
    }

    /**
     * @covers \Bristolian\Repo\RoomVideoRepo\PdoRoomVideoRepo::resolveTagIdsToTags
     */
    public function test_resolveTagIdsToTags_returns_matching_tags_skips_missing_ids(): void
    {
        $repo = $this->injector->make(PdoRoomVideoRepo::class);
        assert($this->roomId !== null);

        $roomTagRepo = $this->injector->make(PdoRoomTagRepo::class);
        $tag = $roomTagRepo->createTag($this->roomId, TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'resolve-tag-' . create_test_uniqid(),
            'description' => 'Desc',
        ])));
        $roomTagsById = [$tag->tag_id => $tag];

        $tags = $repo->resolveTagIdsToTags([], $roomTagsById);
        $this->assertSame([], $tags);

        $tags = $repo->resolveTagIdsToTags([$tag->tag_id], $roomTagsById);
        $this->assertCount(1, $tags);
        $this->assertSame($tag->tag_id, $tags[0]->tag_id);

        $tags = $repo->resolveTagIdsToTags([$tag->tag_id, 'nonexistent-id'], $roomTagsById);
        $this->assertCount(1, $tags);
        $this->assertSame($tag->tag_id, $tags[0]->tag_id);
    }

    /**
     * @covers \Bristolian\Repo\RoomVideoRepo\PdoRoomVideoRepo::fetchVideoById
     */
    public function test_fetchVideoById_returns_video_for_valid_id(): void
    {
        $repo = $this->injector->make(PdoRoomVideoRepo::class);
        assert($this->videoId !== null);

        $video = $repo->fetchVideoById($this->videoId);
        $this->assertInstanceOf(Video::class, $video);
        $this->assertSame($this->videoId, $video->id);
        $this->assertSame('dQw4w9WgXcQ', $video->youtube_video_id);
    }

    /**
     * @covers \Bristolian\Repo\RoomVideoRepo\PdoRoomVideoRepo::fetchVideoById
     */
    public function test_fetchVideoById_throws_for_nonexistent_id(): void
    {
        $repo = $this->injector->make(PdoRoomVideoRepo::class);

        $this->expectException(ContentNotFoundException::class);
        $this->expectExceptionMessage('not found');

        $repo->fetchVideoById('00000000-0000-0000-0000-000000000000');
    }
}
