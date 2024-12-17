<?php

namespace BristolianTest\AppController;

use Bristolian\AppController\Rooms;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;
use SlimDispatcher\Response\JsonResponse;
use Bristolian\Repo\DbInfo\DbInfo;
use Bristolian\Repo\DbInfo\FakeDbInfo;
use VarMap\ArrayVarMap;
use VarMap\VarMap;
use Bristolian\Repo\RoomLinkRepo\RoomLinkRepo;
use Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo;

/**
 * @coversNothing
 */
class RoomsTest extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * @covers \Bristolian\AppController\Rooms::addLink
     */
    public function testRooms_addLink_working()
    {
        $varMap = new ArrayVarMap([
            'url' => 'https://www.example.com/'
        ]);

        $room_id = '123456';

        $userSession = $this->initLoggedInUser([$varMap]);
        $this->initInMemoryFakes();

        $this->injector->defineParam('room_id', $room_id);
        $result = $this->injector->execute('Bristolian\AppController\Rooms::addLink');
        $this->assertInstanceOf(JsonResponse::class, $result);

        $linkRepo = $this->injector->make(FakeRoomLinkRepo::class);
        $added_link = $linkRepo->getLastAddedLink();


        $expected_result = <<< JSON
{
    "status": "success",
    "data": {
        "room_link_id": "{$added_link->id}"
    }
}
JSON;

        $this->assertSame($expected_result, $result->getBody());
    }
}
