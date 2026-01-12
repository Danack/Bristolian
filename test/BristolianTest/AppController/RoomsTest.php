<?php

namespace BristolianTest\AppController;

use Bristolian\AppController\Rooms;
use Bristolian\Parameters\PropertyType\LinkDescription;
use Bristolian\Parameters\PropertyType\LinkTitle;
use Bristolian\Parameters\PropertyType\Url;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;
use SlimDispatcher\Response\JsonResponse;
use Bristolian\Repo\DbInfo\DbInfo;
use Bristolian\Repo\DbInfo\FakeDbInfo;
use VarMap\ArrayVarMap;
use VarMap\VarMap;
use Bristolian\Repo\RoomLinkRepo\RoomLinkRepo;
use Bristolian\Parameters\LinkParam;

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


        $link_param = LinkParam::createFromArray([
            'url' => 'https://www.example.com/',
            'title' => 'link title ' . time(),
            'description' => 'link description ' . time(),
        ]);

        $this->injector->share($link_param);
        $this->injector->defineParam('room_id', $room_id);
        $result = $this->injector->execute('Bristolian\AppController\Rooms::addLink');
//        $this->assertInstanceOf(JsonResponse::class, $result);

        $expected_result = <<< JSON
{
    "result": "success"
}
JSON;

        $this->assertSame($expected_result, $result->getBody());
    }
}
