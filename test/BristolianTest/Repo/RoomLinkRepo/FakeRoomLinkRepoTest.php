<?php

namespace BristolianTest\Repo\RoomLinkRepo;

use Bristolian\Parameters\LinkParam;
use Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;
use VarMap\ArrayVarMap;

class FakeRoomLinkRepoTest extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo
     */
    public function testAddLinkToRoom()
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
    public function testGetLinksForRoom()
    {
        $this->initInMemoryFakes();
        $roomLinkRepo = $this->injector->make(FakeRoomLinkRepo::class);
        $roomLinks = $roomLinkRepo->getLinksForRoom('12345');

        $this->assertEmpty($roomLinks);
    }
}
