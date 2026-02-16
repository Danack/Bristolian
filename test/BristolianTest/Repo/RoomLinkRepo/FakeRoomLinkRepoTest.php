<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomLinkRepo;

use Bristolian\Parameters\LinkParam;
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
        $roomLinks = $roomLinkRepo->getLinksForRoom('12345');

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
        $links = $repo->getLinksForRoom($room_id);
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
}
