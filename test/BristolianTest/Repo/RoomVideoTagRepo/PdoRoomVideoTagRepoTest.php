<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomVideoTagRepo;

use Bristolian\Parameters\TagParams;
use Bristolian\Repo\RoomTagRepo\PdoRoomTagRepo;
use Bristolian\Repo\RoomVideoRepo\PdoRoomVideoRepo;
use Bristolian\Repo\RoomVideoTagRepo\RoomVideoTagRepo;
use BristolianTest\BaseTestCase;
use BristolianTest\Support\HasTestWorld;
use VarMap\ArrayVarMap;

/**
 * @group db
 * @coversNothing
 */
class PdoRoomVideoTagRepoTest extends BaseTestCase
{
    use HasTestWorld;

    private ?string $roomId = null;
    private ?string $roomVideoId = null;
    private ?string $tagId1 = null;
    private ?string $tagId2 = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->ensureStandardSetup();
        $userId = $this->standardTestData()->getTestingUserId();
        $room = $this->world()->roomRepo()->createRoom(
            $userId,
            'T' . time() . '_' . random_int(100, 999),
            'Room video tag tests'
        );
        $this->roomId = $room->id;

        $videoRepo = $this->injector->make(\Bristolian\Repo\VideoRepo\PdoVideoRepo::class);
        $videoId = $videoRepo->create($userId, 'dQw4w9WgXcQ');

        $roomVideoRepo = $this->injector->make(PdoRoomVideoRepo::class);
        $roomVideo = $roomVideoRepo->addVideo($this->roomId, $videoId, 'Video', null);
        $this->roomVideoId = $roomVideo->id;

        $roomTagRepo = $this->injector->make(PdoRoomTagRepo::class);
        $tag1 = $roomTagRepo->createTag($this->roomId, TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'tag-a-' . create_test_uniqid(),
            'description' => 'Tag A',
        ])));
        $tag2 = $roomTagRepo->createTag($this->roomId, TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'tag-b-' . create_test_uniqid(),
            'description' => 'Tag B',
        ])));
        $this->tagId1 = $tag1->tag_id;
        $this->tagId2 = $tag2->tag_id;
    }

    private function getRepo(): RoomVideoTagRepo
    {
        return $this->injector->make(\Bristolian\Repo\RoomVideoTagRepo\PdoRoomVideoTagRepo::class);
    }

    /**
     * @covers \Bristolian\Repo\RoomVideoTagRepo\PdoRoomVideoTagRepo::__construct
     * @covers \Bristolian\Repo\RoomVideoTagRepo\PdoRoomVideoTagRepo::getTagIdsForRoomVideo
     */
    public function test_getTagIdsForRoomVideo_returns_empty_before_set(): void
    {
        $repo = $this->getRepo();
        assert($this->roomVideoId !== null);

        $tagIds = $repo->getTagIdsForRoomVideo($this->roomVideoId);
        $this->assertSame([], $tagIds);
    }

    /**
     * @covers \Bristolian\Repo\RoomVideoTagRepo\PdoRoomVideoTagRepo::setTagsForRoomVideo
     * @covers \Bristolian\Repo\RoomVideoTagRepo\PdoRoomVideoTagRepo::getTagIdsForRoomVideo
     */
    public function test_setTagsForRoomVideo_and_getTagIdsForRoomVideo(): void
    {
        $repo = $this->getRepo();
        assert($this->roomVideoId !== null && $this->tagId1 !== null && $this->tagId2 !== null);

        $repo->setTagsForRoomVideo($this->roomVideoId, [$this->tagId1, $this->tagId2]);

        $tagIds = $repo->getTagIdsForRoomVideo($this->roomVideoId);
        $this->assertCount(2, $tagIds);
        $this->assertContains($this->tagId1, $tagIds);
        $this->assertContains($this->tagId2, $tagIds);
    }

    /**
     * @covers \Bristolian\Repo\RoomVideoTagRepo\PdoRoomVideoTagRepo::setTagsForRoomVideo
     * @covers \Bristolian\Repo\RoomVideoTagRepo\PdoRoomVideoTagRepo::getTagIdsForRoomVideo
     */
    public function test_setTagsForRoomVideo_replaces_existing(): void
    {
        $repo = $this->getRepo();
        assert($this->roomVideoId !== null && $this->tagId1 !== null && $this->tagId2 !== null);

        $repo->setTagsForRoomVideo($this->roomVideoId, [$this->tagId1]);
        $repo->setTagsForRoomVideo($this->roomVideoId, [$this->tagId2]);

        $tagIds = $repo->getTagIdsForRoomVideo($this->roomVideoId);
        $this->assertCount(1, $tagIds);
        $this->assertSame($this->tagId2, $tagIds[0]);
    }
}
