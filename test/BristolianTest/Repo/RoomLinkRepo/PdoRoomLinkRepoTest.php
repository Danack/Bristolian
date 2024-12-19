<?php

namespace BristolianTest\Repo\RoomLinkRepo;

use Bristolian\DataType\LinkParam;
use Bristolian\Model\RoomLink;
use Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo;
use BristolianTest\Repo\TestPlaceholders;
use PHPUnit\Framework\TestCase;
use VarMap\ArrayVarMap;

class PdoRoomLinkRepoTest extends TestCase
{
    use TestPlaceholders;

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo
     */
    public function testAddLinkToRoom()
    {
        $this->initPdoTestObjects();
        [$room, $user] = $this->createTestUserAndRoom();
        $roomLinkRepo = $this->injector->make(PdoRoomLinkRepo::class);

        $url = $this->getTestLink();

        $room_link_id = $roomLinkRepo->addLinkToRoomFromParam(
            $user->getUserId(),
            $room->getRoomId(),
            LinkParam::createFromArray([
                'url' => $url
            ])
        );

        $room_link = $roomLinkRepo->getRoomLink($room_link_id);
        $this->assertSame($room_link_id, $room_link->id);
        $this->assertSame($url, $room_link->url);
    }

//    /**
//     * @covers \Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo
//     */
//    public function testGetLinksForRoom()
//    {
////        $this->initInMemoryFakes();
////        $roomLinkRepo = $this->injector->make(FakeRoomLinkRepo::class);
////        $roomLinks = $roomLinkRepo->getLinksForRoom('12345');
////
////        $this->assertEmpty($roomLinks);
//    }
}
