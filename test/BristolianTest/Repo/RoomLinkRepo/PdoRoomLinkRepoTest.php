<?php

namespace BristolianTest\Repo\RoomLinkRepo;

use Bristolian\Exception\BristolianException;
use Bristolian\Parameters\LinkParam;
use Bristolian\Parameters\RoomContentSearchParams;
use Bristolian\Parameters\TagParams;
use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Repo\LinkRepo\LinkRepo;
use Bristolian\Repo\LinkRepo\PdoLinkRepo;
use Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo;
use Bristolian\Repo\RoomLinkRepo\RoomLinkRepo;
use Bristolian\Repo\RoomLinkTagRepo\PdoRoomLinkTagRepo;
use Bristolian\Repo\RoomTagRepo\PdoRoomTagRepo;
use BristolianTest\Repo\TestPlaceholders;
use BristolianTest\Support\HasTestWorld;
use Bristolian\Model\Generated\RoomLink;
use VarMap\ArrayVarMap;

/**
 * @group db
 * @coversNothing
 */
class PdoRoomLinkRepoTest extends RoomLinkRepoFixture
{
    use HasTestWorld;
    use TestPlaceholders;

    private ?LinkRepo $linkRepo = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->initPdoTestObjects();
    }

    public function getTestInstance(LinkRepo $linkRepo): RoomLinkRepo
    {
        // initPdoTestObjects() already aliased and shared LinkRepo/PdoLinkRepo; use it.
        return $this->injector->make(PdoRoomLinkRepo::class);
    }

    protected function getLinkRepo(): LinkRepo
    {
        if ($this->linkRepo === null) {
            $this->linkRepo = $this->injector->make(PdoLinkRepo::class);
        }
        return $this->linkRepo;
    }

    protected function getValidUserId(): string
    {
        $this->ensureStandardSetup();
        return $this->standardTestData()->getTestingUserId();
    }

    protected function getValidRoomId(): string
    {
        $this->ensureStandardSetup();
        return $this->standardTestData()->getHousingRoom()->id;
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo
     */
    public function testAddLinkToRoom(): void
    {
        $this->initPdoTestObjects();
        [$room, $user] = $this->createTestUserAndRoom();
        $roomLinkRepo = $this->injector->make(PdoRoomLinkRepo::class);

        $url = $this->getTestLink();

        $room_link_id = $roomLinkRepo->addLinkToRoomFromParam(
            $user->getUserId(),
            $room->id,
            LinkParam::createFromArray([
                'url' => $url
            ])
        );

        $room_link = $roomLinkRepo->getRoomLink($room_link_id);
        $this->assertSame($room_link_id, $room_link->id);
        // url is in Link table, not RoomLink
        $this->assertNull($room_link->title);
        $this->assertNull($room_link->description);
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo
     */
    public function testAddLinkToRoomWithTitleAndDescription(): void
    {
        $this->initPdoTestObjects();
        [$room, $user] = $this->createTestUserAndRoom();
        $roomLinkRepo = $this->injector->make(PdoRoomLinkRepo::class);

        $url = $this->getTestLink();
        $title = 'Test Link Title';
        $description = 'Test Link Description';

        $room_link_id = $roomLinkRepo->addLinkToRoomFromParam(
            $user->getUserId(),
            $room->id,
            LinkParam::createFromArray([
                'url' => $url,
                'title' => $title,
                'description' => $description
            ])
        );

        $room_link = $roomLinkRepo->getRoomLink($room_link_id);
        $this->assertSame($room_link_id, $room_link->id);
        // url and user_id are in Link table, not RoomLink
        $this->assertSame($title, $room_link->title);
        $this->assertSame($description, $room_link->description);
        $this->assertSame($room->id, $room_link->room_id);
        $this->assertInstanceOf(\DateTimeInterface::class, $room_link->created_at);
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo
     */
    public function testGetLinksForRoom(): void
    {
        $this->initPdoTestObjects();
        [$room, $user] = $this->createTestUserAndRoom();
        $roomLinkRepo = $this->injector->make(PdoRoomLinkRepo::class);

        // Initially room should have no links
        $roomLinks = $roomLinkRepo->getLinksForRoom($room->id, \Bristolian\Parameters\RoomContentSearchParams::default());
        $this->assertEmpty($roomLinks);

        // Add first link
        $url1 = $this->getTestLink();
        $title1 = 'First Link';
        $room_link_id_1 = $roomLinkRepo->addLinkToRoomFromParam(
            $user->getUserId(),
            $room->id,
            LinkParam::createFromArray([
                'url' => $url1,
                'title' => $title1
            ])
        );

        // Add second link
        $url2 = $this->getTestLink();
        $title2 = 'Second Link';
        $room_link_id_2 = $roomLinkRepo->addLinkToRoomFromParam(
            $user->getUserId(),
            $room->id,
            LinkParam::createFromArray([
                'url' => $url2,
                'title' => $title2
            ])
        );

        // Retrieve all links for the room
        $roomLinks = $roomLinkRepo->getLinksForRoom($room->id, \Bristolian\Parameters\RoomContentSearchParams::default());

        $this->assertCount(2, $roomLinks);
        $this->assertContainsOnlyInstancesOf(RoomLink::class, $roomLinks);

        // Verify first link
        $link1 = array_filter($roomLinks, fn($link) => $link->id === $room_link_id_1);
        $link1 = array_values($link1)[0];
        // url is in Link table, not RoomLink
        $this->assertSame($title1, $link1->title);

        // Verify second link
        $link2 = array_filter($roomLinks, fn($link) => $link->id === $room_link_id_2);
        $link2 = array_values($link2)[0];
        // url is in Link table, not RoomLink
        $this->assertSame($title2, $link2->title);
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo
     */
    public function testGetLinksForRoomReturnsEmptyArrayForNonExistentRoom(): void
    {
        $this->initPdoTestObjects();
        $roomLinkRepo = $this->injector->make(PdoRoomLinkRepo::class);

        // Try to get links for a room that doesn't exist
        $roomLinks = $roomLinkRepo->getLinksForRoom('non-existent-room-id', \Bristolian\Parameters\RoomContentSearchParams::default());

        $this->assertEmpty($roomLinks);
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo
     */
    public function testGetRoomLinkThrowsExceptionForNonExistentLink(): void
    {
        $this->initPdoTestObjects();
        $roomLinkRepo = $this->injector->make(PdoRoomLinkRepo::class);

        $this->expectException(BristolianException::class);
        $this->expectExceptionMessage("Failed to find room link with 'id' => non-existent-link-id");

        $roomLinkRepo->getRoomLink('non-existent-link-id');
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo
     */
    public function testMultipleLinksFromDifferentUsers(): void
    {
        $this->initPdoTestObjects();
        [$room, $user1] = $this->createTestUserAndRoom();
        $user2 = $this->createTestAdminUser();
        $roomLinkRepo = $this->injector->make(PdoRoomLinkRepo::class);

        // Add link from first user
        $url1 = $this->getTestLink();
        $room_link_id_1 = $roomLinkRepo->addLinkToRoomFromParam(
            $user1->getUserId(),
            $room->id,
            LinkParam::createFromArray(['url' => $url1])
        );

        // Add link from second user
        $url2 = $this->getTestLink();
        $room_link_id_2 = $roomLinkRepo->addLinkToRoomFromParam(
            $user2->getUserId(),
            $room->id,
            LinkParam::createFromArray(['url' => $url2])
        );

        // Retrieve both links
        $link1 = $roomLinkRepo->getRoomLink($room_link_id_1);
        $link2 = $roomLinkRepo->getRoomLink($room_link_id_2);

        // user_id is in Link table, not RoomLink - verify link_id exists instead
        $this->assertNotNull($link1->link_id);
        $this->assertNotNull($link2->link_id);

        // Both links should be in the same room
        $this->assertSame($room->id, $link1->room_id);
        $this->assertSame($room->id, $link2->room_id);

        // Verify both appear in the room's links
        $roomLinks = $roomLinkRepo->getLinksForRoom($room->id, RoomContentSearchParams::default());
        $this->assertCount(2, $roomLinks);
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo::getLinksForRoom
     */
    public function test_getLinksForRoom_filters_by_title(): void
    {
        $this->initPdoTestObjects();
        [$room, $user] = $this->createTestUserAndRoom();
        $roomLinkRepo = $this->injector->make(PdoRoomLinkRepo::class);
        $roomLinkRepo->addLinkToRoomFromParam($user->getUserId(), $room->id, LinkParam::createFromArray([
            'url' => $this->getTestLink(),
            'title' => 'Report unique_title_slug ' . create_test_uniqid(),
        ]));

        $search = RoomContentSearchParams::createFromVarMap(new ArrayVarMap(['title' => 'unique_title_slug']));
        $links = $roomLinkRepo->getLinksForRoom($room->id, $search);

        $this->assertCount(1, $links);
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo::getLinksForRoom
     */
    public function test_getLinksForRoom_filters_by_created_at_after(): void
    {
        $this->initPdoTestObjects();
        [$room, $user] = $this->createTestUserAndRoom();
        $roomLinkRepo = $this->injector->make(PdoRoomLinkRepo::class);
        $roomLinkRepo->addLinkToRoomFromParam($user->getUserId(), $room->id, LinkParam::createFromArray(['url' => $this->getTestLink()]));

        $future = (new \DateTimeImmutable('now'))->modify('+1 day')->format('Y-m-d H:i:s');
        $search = RoomContentSearchParams::createFromVarMap(new ArrayVarMap(['created_at_after' => $future]));
        $links = $roomLinkRepo->getLinksForRoom($room->id, $search);

        $this->assertCount(0, $links);
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo::getLinksForRoom
     */
    public function test_getLinksForRoom_filters_by_created_at_before(): void
    {
        $this->initPdoTestObjects();
        [$room, $user] = $this->createTestUserAndRoom();
        $roomLinkRepo = $this->injector->make(PdoRoomLinkRepo::class);
        $roomLinkRepo->addLinkToRoomFromParam($user->getUserId(), $room->id, LinkParam::createFromArray(['url' => $this->getTestLink()]));

        $past = (new \DateTimeImmutable('now'))->modify('-1 day')->format('Y-m-d H:i:s');
        $search = RoomContentSearchParams::createFromVarMap(new ArrayVarMap(['created_at_before' => $past]));
        $links = $roomLinkRepo->getLinksForRoom($room->id, $search);

        $this->assertCount(0, $links);
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo::getLinksForRoom
     */
    public function test_getLinksForRoom_filters_by_document_timestamp_after(): void
    {
        $this->initPdoTestObjects();
        [$room, $user] = $this->createTestUserAndRoom();
        $roomLinkRepo = $this->injector->make(PdoRoomLinkRepo::class);
        $roomLinkId = $roomLinkRepo->addLinkToRoomFromParam($user->getUserId(), $room->id, LinkParam::createFromArray(['url' => $this->getTestLink()]));

        $pdoSimple = $this->injector->make(PdoSimple::class);
        $pdoSimple->execute(
            'UPDATE room_link SET document_timestamp = :ts WHERE id = :id',
            [':ts' => '2024-06-01 12:00:00', ':id' => $roomLinkId]
        );

        $search = RoomContentSearchParams::createFromVarMap(new ArrayVarMap(['document_timestamp_after' => '2024-06-02 00:00:00']));
        $links = $roomLinkRepo->getLinksForRoom($room->id, $search);

        $this->assertCount(0, $links);
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo::getLinksForRoom
     */
    public function test_getLinksForRoom_filters_by_document_timestamp_before(): void
    {
        $this->initPdoTestObjects();
        [$room, $user] = $this->createTestUserAndRoom();
        $roomLinkRepo = $this->injector->make(PdoRoomLinkRepo::class);
        $roomLinkId = $roomLinkRepo->addLinkToRoomFromParam($user->getUserId(), $room->id, LinkParam::createFromArray(['url' => $this->getTestLink()]));

        $pdoSimple = $this->injector->make(PdoSimple::class);
        $pdoSimple->execute(
            'UPDATE room_link SET document_timestamp = :ts WHERE id = :id',
            [':ts' => '2024-06-15 12:00:00', ':id' => $roomLinkId]
        );

        $search = RoomContentSearchParams::createFromVarMap(new ArrayVarMap(['document_timestamp_before' => '2024-06-01 00:00:00']));
        $links = $roomLinkRepo->getLinksForRoom($room->id, $search);

        $this->assertCount(0, $links);
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo::getLinksForRoom
     */
    public function test_getLinksForRoom_filters_by_tag_ids(): void
    {
        $this->initPdoTestObjects();
        [$room, $user] = $this->createTestUserAndRoom();
        $roomLinkRepo = $this->injector->make(PdoRoomLinkRepo::class);
        $roomLinkId = $roomLinkRepo->addLinkToRoomFromParam($user->getUserId(), $room->id, LinkParam::createFromArray(['url' => $this->getTestLink()]));

        $roomTagRepo = $this->injector->make(PdoRoomTagRepo::class);
        $tag = $roomTagRepo->createTag($room->id, TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'link-tag-' . create_test_uniqid(),
            'description' => 'Tag for link filter test',
        ])));
        $roomLinkTagRepo = $this->injector->make(PdoRoomLinkTagRepo::class);
        $roomLinkTagRepo->setTagsForRoomLink($roomLinkId, [$tag->tag_id]);

        $search = RoomContentSearchParams::createFromVarMap(new ArrayVarMap(['tag_ids' => $tag->tag_id]));
        $links = $roomLinkRepo->getLinksForRoom($room->id, $search);

        $this->assertCount(1, $links);
        $this->assertSame($roomLinkId, $links[0]->id);
    }
}
