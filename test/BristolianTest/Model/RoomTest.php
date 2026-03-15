<?php

namespace BristolianTest\Model;

use BristolianTest\BaseTestCase;
use Bristolian\Model\Generated\Room;

/**
 * @coversNothing
 */
class RoomTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\Generated\Room
     */
    public function testConstruct()
    {
        $id = 'room-123';
        $ownerUserId = 'user-456';
        $name = 'Test Room';
        $purpose = 'A room for testing';
        $created_at = new \DateTime();

        $room = new Room($id, $ownerUserId, $name, $purpose, $created_at);

        $this->assertSame($id, $room->id);
        $this->assertSame($ownerUserId, $room->owner_user_id);
        $this->assertSame($name, $room->name);
        $this->assertSame($purpose, $room->purpose);
        $this->assertSame($created_at, $room->created_at);
    }

    /**
     * @covers \Bristolian\Model\Generated\Room
     */
    public function testToArray()
    {
        $created_at = new \DateTime();

        $room = new Room('id', 'owner', 'name', 'purpose', $created_at);
        $array = $room->toArray();

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('name', $array);
    }
}
