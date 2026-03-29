<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomLinkRepo;

use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Model\Generated\RoomLink;
use Bristolian\Parameters\LinkParam;
use Bristolian\Parameters\RoomContentSearchParams;
use Bristolian\Repo\LinkRepo\LinkRepo;
use Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo;
use Bristolian\Repo\RoomLinkRepo\RoomLinkRepo;
use BristolianTest\Repo\TestPlaceholders;
use VarMap\ArrayVarMap;

/**
 * @group standard_repo
 * @coversNothing
 */
class FakeRoomLinkRepoTest extends RoomLinkRepoFixture
{
    use TestPlaceholders;

    /**
     * @return RoomLinkRepo
     */
    public function getTestInstance(LinkRepo $linkRepo): RoomLinkRepo
    {
        return new FakeRoomLinkRepo($linkRepo);
    }

    protected function getLinkRepo(): LinkRepo
    {
        return new \Bristolian\Repo\LinkRepo\FakeLinkRepo();
    }

    protected function getValidUserId(): string
    {
        return 'user_123';
    }

    protected function getValidRoomId(): string
    {
        return 'room_456';
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo
     */
    public function testAddLinkToRoom(): void
    {
        $varMap = new ArrayVarMap([
            'url' => 'https://www.example.com/'
        ]);

        $room_id = '123456';
        $url = $this->getTestLink();

        $userSession = $this->initLoggedInUser([$varMap]);
        $this->initInMemoryFakes();

        $roomLinkRepo = $this->injector->make(FakeRoomLinkRepo::class);

        $roomLinkId = $roomLinkRepo->addLinkToRoomFromParam(
            $userSession->getUserId(),
            $room_id,
            LinkParam::createFromArray([
                'url' => $url
            ])
        );

        $roomLink = $roomLinkRepo->getRoomLink($roomLinkId);
        // RoomLink no longer has user_id or url properties - these are in the Link table
        $this->assertNotNull($roomLink);
        $this->assertSame($room_id, $roomLink->room_id);
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo
     */
    public function testGetLinksForRoom(): void
    {
        $this->initInMemoryFakes();
        $roomLinkRepo = $this->injector->make(FakeRoomLinkRepo::class);
        $roomLinks = $roomLinkRepo->getLinksForRoom('12345', RoomContentSearchParams::default());

        $this->assertEmpty($roomLinks);
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo::getLinksForRoom
     * @covers \Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo::addLinkToRoomFromParam
     * @covers \Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo::getLastAddedLink
     */
    public function test_getLinksForRoom_and_getLastAddedLink_after_add(): void
    {
        $linkRepo = new \Bristolian\Repo\LinkRepo\FakeLinkRepo();
        $repo = new FakeRoomLinkRepo($linkRepo);
        $room_id = 'room-1';
        $params = LinkParam::createFromVarMap(new ArrayVarMap([
            'url' => 'https://example.com',
            'title' => 'Example Title Here',
            'description' => 'Description text here',
        ]));
        $repo->addLinkToRoomFromParam('user-1', $room_id, $params);
        $links = $repo->getLinksForRoom($room_id, RoomContentSearchParams::default());
        $this->assertCount(1, $links);
        $last = $repo->getLastAddedLink();
        $this->assertNotNull($last);
        $this->assertSame($room_id, $last->room_id);
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo::getLastAddedLink
     */
    public function test_getLastAddedLink_returns_null_when_empty(): void
    {
        $linkRepo = new \Bristolian\Repo\LinkRepo\FakeLinkRepo();
        $repo = new FakeRoomLinkRepo($linkRepo);
        $this->assertNull($repo->getLastAddedLink());
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo::getRoomLink
     */
    public function test_getRoomLink_returns_null_for_unknown_id(): void
    {
        $linkRepo = new \Bristolian\Repo\LinkRepo\FakeLinkRepo();
        $repo = new FakeRoomLinkRepo($linkRepo);
        $this->assertNull($repo->getRoomLink('nonexistent-link-id'));
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo::getLinksForRoom
     * @covers \Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo::filterLinksBySearch
     */
    public function test_getLinksForRoom_filters_by_title(): void
    {
        $linkRepo = new \Bristolian\Repo\LinkRepo\FakeLinkRepo();
        $repo = new FakeRoomLinkRepo($linkRepo);
        $roomId = 'room-1';
        $repo->addLinkToRoomFromParam('user-1', $roomId, LinkParam::createFromVarMap(new ArrayVarMap([
            'url' => 'https://example.com/a',
            'title' => 'First link',
        ])));
        $repo->addLinkToRoomFromParam('user-1', $roomId, LinkParam::createFromVarMap(new ArrayVarMap([
            'url' => 'https://example.com/b',
            'title' => 'Report with unique slug here',
        ])));

        $search = RoomContentSearchParams::createFromVarMap(new ArrayVarMap(['title' => 'unique slug']));
        $links = $repo->getLinksForRoom($roomId, $search);

        $this->assertCount(1, $links);
        $this->assertSame('Report with unique slug here', $links[0]->title);
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo::getLinksForRoom
     * @covers \Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo::filterLinksBySearch
     */
    public function test_getLinksForRoom_filters_by_description(): void
    {
        $linkRepo = new \Bristolian\Repo\LinkRepo\FakeLinkRepo();
        $repo = new FakeRoomLinkRepo($linkRepo);
        $roomId = 'room-1';
        $repo->addLinkToRoomFromParam('user-1', $roomId, LinkParam::createFromVarMap(new ArrayVarMap([
            'url' => 'https://example.com/a',
            'title' => 'First link',
            'description' => 'Some description',
        ])));
        $repo->addLinkToRoomFromParam('user-1', $roomId, LinkParam::createFromVarMap(new ArrayVarMap([
            'url' => 'https://example.com/b',
            'title' => 'Other title',
            'description' => 'Report with unique desc slug here',
        ])));

        $search = RoomContentSearchParams::createFromVarMap(new ArrayVarMap(['description' => 'unique desc slug']));
        $links = $repo->getLinksForRoom($roomId, $search);

        $this->assertCount(1, $links);
        $this->assertSame('Report with unique desc slug here', $links[0]->description);
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo::getLinksForRoom
     * @covers \Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo::filterLinksBySearch
     */
    public function test_getLinksForRoom_filters_by_created_at_after(): void
    {
        $linkRepo = new \Bristolian\Repo\LinkRepo\FakeLinkRepo();
        $repo = new FakeRoomLinkRepo($linkRepo);
        $roomId = 'room-1';
        $repo->addLinkToRoomFromParam('user-1', $roomId, LinkParam::createFromVarMap(new ArrayVarMap(['url' => 'https://example.com'])));

        $search = RoomContentSearchParams::createFromVarMap(new ArrayVarMap(['created_at_after' => '2010-01-29 00:00:00']));
        $links = $repo->getLinksForRoom($roomId, $search);

        $this->assertCount(0, $links);
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo::getLinksForRoom
     * @covers \Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo::filterLinksBySearch
     */
    public function test_getLinksForRoom_filters_by_created_at_before(): void
    {
        $linkRepo = new \Bristolian\Repo\LinkRepo\FakeLinkRepo();
        $repo = new FakeRoomLinkRepo($linkRepo);
        $roomId = 'room-1';
        $repo->addLinkToRoomFromParam('user-1', $roomId, LinkParam::createFromVarMap(new ArrayVarMap(['url' => 'https://example.com'])));

        $search = RoomContentSearchParams::createFromVarMap(new ArrayVarMap(['created_at_before' => '2010-01-27 00:00:00']));
        $links = $repo->getLinksForRoom($roomId, $search);

        $this->assertCount(0, $links);
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo::getLinksForRoom
     * @covers \Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo::filterLinksBySearch
     * @covers \Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo::setDocumentTimestampForRoomLink
     */
    public function test_getLinksForRoom_filters_by_document_timestamp_after(): void
    {
        $linkRepo = new \Bristolian\Repo\LinkRepo\FakeLinkRepo();
        $repo = new FakeRoomLinkRepo($linkRepo);
        $roomId = 'room-1';
        $roomLinkId = $repo->addLinkToRoomFromParam('user-1', $roomId, LinkParam::createFromVarMap(new ArrayVarMap(['url' => 'https://example.com'])));
        $repo->setDocumentTimestampForRoomLink($roomLinkId, new \DateTimeImmutable('2024-06-01 12:00:00'));

        $search = RoomContentSearchParams::createFromVarMap(new ArrayVarMap(['document_timestamp_after' => '2024-06-02 00:00:00']));
        $links = $repo->getLinksForRoom($roomId, $search);

        $this->assertCount(0, $links);
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo::getLinksForRoom
     * @covers \Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo::filterLinksBySearch
     * @covers \Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo::setDocumentTimestampForRoomLink
     */
    public function test_getLinksForRoom_filters_by_document_timestamp_before(): void
    {
        $linkRepo = new \Bristolian\Repo\LinkRepo\FakeLinkRepo();
        $repo = new FakeRoomLinkRepo($linkRepo);
        $roomId = 'room-1';
        $roomLinkId = $repo->addLinkToRoomFromParam('user-1', $roomId, LinkParam::createFromVarMap(new ArrayVarMap(['url' => 'https://example.com'])));
        $repo->setDocumentTimestampForRoomLink($roomLinkId, new \DateTimeImmutable('2024-06-15 12:00:00'));

        $search = RoomContentSearchParams::createFromVarMap(new ArrayVarMap(['document_timestamp_before' => '2024-06-01 00:00:00']));
        $links = $repo->getLinksForRoom($roomId, $search);

        $this->assertCount(0, $links);
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo::updateTitleAndDescription
     * @covers \Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo::addLinkToRoomFromParam
     */
    public function test_updateTitleAndDescription_throws_when_room_mismatches(): void
    {
        $linkRepo = new \Bristolian\Repo\LinkRepo\FakeLinkRepo();
        $repo = new FakeRoomLinkRepo($linkRepo);
        $roomLinkId = $repo->addLinkToRoomFromParam('user-1', 'room-a', LinkParam::createFromVarMap(new ArrayVarMap([
            'url' => 'https://example.com/' . create_test_uniqid(),
        ])));
        $this->expectException(ContentNotFoundException::class);
        $this->expectExceptionMessage('Link not found in room');
        $repo->updateTitleAndDescription('room-b', $roomLinkId, 'Title', 'Description');
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo::updateTitleAndDescription
     * @covers \Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo::addLinkToRoomFromParam
     * @covers \Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo::getRoomLink
     */
    public function test_updateTitleAndDescription_iterates_past_other_link_ids(): void
    {
        $linkRepo = new \Bristolian\Repo\LinkRepo\FakeLinkRepo();
        $repo = new FakeRoomLinkRepo($linkRepo);
        $roomId = 'room-shared';
        $firstId = $repo->addLinkToRoomFromParam('user-1', $roomId, LinkParam::createFromVarMap(new ArrayVarMap([
            'url' => 'https://example.com/first-' . create_test_uniqid(),
        ])));
        $secondId = $repo->addLinkToRoomFromParam('user-1', $roomId, LinkParam::createFromVarMap(new ArrayVarMap([
            'url' => 'https://example.com/second-' . create_test_uniqid(),
        ])));
        $newTitle = 'Updated second ' . create_test_uniqid();
        $repo->updateTitleAndDescription($roomId, $secondId, $newTitle, null);
        $this->assertNull($repo->getRoomLink($firstId)->title);
        $this->assertSame($newTitle, $repo->getRoomLink($secondId)->title);
    }
}
