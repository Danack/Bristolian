<?php

namespace BristolianTest\Repo\RoomSourceLinkRepo;

use Bristolian\Repo\RoomSourceLinkRepo\PdoRoomSourceLinkRepo;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;
use Bristolian\DataType\SourceLinkParam;

/**
 * @covers \Bristolian\Repo\RoomSourceLinkRepo\PdoRoomSourceLinkRepo
 * @group db
 */
class PdoRoomSourceLinkRepoTest extends BaseTestCase
{
    use TestPlaceholders;

    public function testWorks()
    {
        $pdoRoomSourceLinkRepo = $this->injector->make(PdoRoomSourceLinkRepo::class);

        [$room, $user] = $this->createTestUserAndRoom();
        $file_id = $this->createTestFile($user);

        $highlights =[
            [
                'page' => 0,
                'left' => 10,
                'top' => 20,
                'right' => 200,
                'bottom' => 30,
            ],
            [
                'page' => 0,
                'left' => 10,
                'top' => 20,
                'right' => 200,
                'bottom' => 30,
            ]
        ];

        $sourcelink_data = [
            'title' => "I am an example highlight",
            'text' => "This is some highlighted text",
            'highlights_json' => json_encode_safe($highlights)
        ];
        $sourcelink_param = SourceLinkParam::createFromArray($sourcelink_data);

        $pdoRoomSourceLinkRepo->addSourceLink(
            $user->getUserId(),
            $room->getRoomId(),
            $file_id,
            $sourcelink_param
        );
    }
}
