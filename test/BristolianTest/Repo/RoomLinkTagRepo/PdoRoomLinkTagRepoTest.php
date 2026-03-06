<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomLinkTagRepo;

use Bristolian\Parameters\LinkParam;
use Bristolian\Parameters\TagParams;
use Bristolian\Repo\RoomLinkTagRepo\PdoRoomLinkTagRepo;
use Bristolian\Repo\RoomLinkTagRepo\RoomLinkTagRepo;
use Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo;
use Bristolian\Repo\RoomTagRepo\PdoRoomTagRepo;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;
use BristolianTest\Support\HasTestWorld;
use VarMap\ArrayVarMap;

/**
 * @group db
 * @coversNothing
 */
class PdoRoomLinkTagRepoTest extends BaseTestCase
{
    use HasTestWorld;
    use TestPlaceholders;

    private ?string $roomLinkId = null;
    private ?string $tagId1 = null;
    private ?string $tagId2 = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->initPdoTestObjects();
        $this->ensureStandardSetup();
        [$room, $user] = $this->createTestUserAndRoom();
        $roomLinkRepo = $this->injector->make(PdoRoomLinkRepo::class);
        $this->roomLinkId = $roomLinkRepo->addLinkToRoomFromParam(
            $user->getUserId(),
            $room->id,
            LinkParam::createFromArray(['url' => $this->getTestLink()])
        );
        $roomTagRepo = $this->injector->make(PdoRoomTagRepo::class);
        $tag1 = $roomTagRepo->createTag($room->id, TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'linktag-a-' . create_test_uniqid(),
            'description' => 'Tag A',
        ])));
        $tag2 = $roomTagRepo->createTag($room->id, TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'linktag-b-' . create_test_uniqid(),
            'description' => 'Tag B',
        ])));
        $this->tagId1 = $tag1->tag_id;
        $this->tagId2 = $tag2->tag_id;
    }

    private function getRepo(): RoomLinkTagRepo
    {
        return $this->injector->make(PdoRoomLinkTagRepo::class);
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkTagRepo\PdoRoomLinkTagRepo::__construct
     * @covers \Bristolian\Repo\RoomLinkTagRepo\PdoRoomLinkTagRepo::getTagIdsForRoomLink
     */
    public function test_getTagIdsForRoomLink_returns_empty_before_set(): void
    {
        $repo = $this->getRepo();
        assert($this->roomLinkId !== null);
        $this->assertSame([], $repo->getTagIdsForRoomLink($this->roomLinkId));
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkTagRepo\PdoRoomLinkTagRepo::setTagsForRoomLink
     * @covers \Bristolian\Repo\RoomLinkTagRepo\PdoRoomLinkTagRepo::getTagIdsForRoomLink
     */
    public function test_setTagsForRoomLink_and_getTagIdsForRoomLink_roundtrip(): void
    {
        $repo = $this->getRepo();
        assert($this->roomLinkId !== null && $this->tagId1 !== null && $this->tagId2 !== null);
        $repo->setTagsForRoomLink($this->roomLinkId, [$this->tagId1, $this->tagId2]);
        $ids = $repo->getTagIdsForRoomLink($this->roomLinkId);
        $this->assertCount(2, $ids);
        $this->assertContains($this->tagId1, $ids);
        $this->assertContains($this->tagId2, $ids);
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkTagRepo\PdoRoomLinkTagRepo::setTagsForRoomLink
     * @covers \Bristolian\Repo\RoomLinkTagRepo\PdoRoomLinkTagRepo::getTagIdsForRoomLink
     */
    public function test_setTagsForRoomLink_replaces_existing(): void
    {
        $repo = $this->getRepo();
        assert($this->roomLinkId !== null && $this->tagId1 !== null && $this->tagId2 !== null);
        $repo->setTagsForRoomLink($this->roomLinkId, [$this->tagId1]);
        $repo->setTagsForRoomLink($this->roomLinkId, [$this->tagId2]);
        $ids = $repo->getTagIdsForRoomLink($this->roomLinkId);
        $this->assertCount(1, $ids);
        $this->assertSame($this->tagId2, $ids[0]);
    }
}
