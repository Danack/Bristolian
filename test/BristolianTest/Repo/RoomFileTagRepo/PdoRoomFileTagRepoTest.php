<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomFileTagRepo;

use Bristolian\Parameters\TagParams;
use Bristolian\Repo\RoomFileTagRepo\PdoRoomFileTagRepo;
use Bristolian\Repo\RoomFileTagRepo\RoomFileTagRepo;
use Bristolian\Repo\RoomFileRepo\PdoRoomFileRepo;
use Bristolian\Repo\RoomTagRepo\PdoRoomTagRepo;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;
use BristolianTest\Support\HasTestWorld;
use VarMap\ArrayVarMap;

/**
 * @group db
 * @coversNothing
 */
class PdoRoomFileTagRepoTest extends BaseTestCase
{
    use HasTestWorld;
    use TestPlaceholders;

    private ?string $roomId = null;
    private ?string $storedFileId = null;
    private ?string $tagId1 = null;
    private ?string $tagId2 = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->ensureStandardSetup();
        [$room, $user] = $this->createTestUserAndRoom();
        $this->roomId = $room->id;
        $this->storedFileId = $this->createTestFile($user);
        $roomFileRepo = $this->injector->make(PdoRoomFileRepo::class);
        $roomFileRepo->addFileToRoom($this->storedFileId, $room->id);
        $roomTagRepo = $this->injector->make(PdoRoomTagRepo::class);
        $tag1 = $roomTagRepo->createTag($room->id, TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'filetag-a-' . create_test_uniqid(),
            'description' => 'Tag A',
        ])));
        $tag2 = $roomTagRepo->createTag($room->id, TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'filetag-b-' . create_test_uniqid(),
            'description' => 'Tag B',
        ])));
        $this->tagId1 = $tag1->tag_id;
        $this->tagId2 = $tag2->tag_id;
    }

    private function getRepo(): RoomFileTagRepo
    {
        return $this->injector->make(PdoRoomFileTagRepo::class);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileTagRepo\PdoRoomFileTagRepo::__construct
     * @covers \Bristolian\Repo\RoomFileTagRepo\PdoRoomFileTagRepo::getTagIdsForRoomFile
     */
    public function test_getTagIdsForRoomFile_returns_empty_before_set(): void
    {
        $repo = $this->getRepo();
        assert($this->roomId !== null && $this->storedFileId !== null);
        $this->assertSame([], $repo->getTagIdsForRoomFile($this->roomId, $this->storedFileId));
    }

    /**
     * @covers \Bristolian\Repo\RoomFileTagRepo\PdoRoomFileTagRepo::setTagsForRoomFile
     * @covers \Bristolian\Repo\RoomFileTagRepo\PdoRoomFileTagRepo::getTagIdsForRoomFile
     */
    public function test_setTagsForRoomFile_and_getTagIdsForRoomFile_roundtrip(): void
    {
        $repo = $this->getRepo();
        assert($this->roomId !== null && $this->storedFileId !== null && $this->tagId1 !== null && $this->tagId2 !== null);
        $repo->setTagsForRoomFile($this->roomId, $this->storedFileId, [$this->tagId1, $this->tagId2]);
        $ids = $repo->getTagIdsForRoomFile($this->roomId, $this->storedFileId);
        $this->assertCount(2, $ids);
        $this->assertContains($this->tagId1, $ids);
        $this->assertContains($this->tagId2, $ids);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileTagRepo\PdoRoomFileTagRepo::setTagsForRoomFile
     * @covers \Bristolian\Repo\RoomFileTagRepo\PdoRoomFileTagRepo::getTagIdsForRoomFile
     */
    public function test_setTagsForRoomFile_replaces_existing(): void
    {
        $repo = $this->getRepo();
        assert($this->roomId !== null && $this->storedFileId !== null && $this->tagId1 !== null && $this->tagId2 !== null);
        $repo->setTagsForRoomFile($this->roomId, $this->storedFileId, [$this->tagId1]);
        $repo->setTagsForRoomFile($this->roomId, $this->storedFileId, [$this->tagId2]);
        $ids = $repo->getTagIdsForRoomFile($this->roomId, $this->storedFileId);
        $this->assertCount(1, $ids);
        $this->assertSame($this->tagId2, $ids[0]);
    }
}
