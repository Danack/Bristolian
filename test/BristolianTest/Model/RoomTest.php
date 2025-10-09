<?php

namespace BristolianTest\Model;

use BristolianTest\BaseTestCase;
use Bristolian\Model\Room;

/**
 * @coversNothing
 */
class RoomTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\Room
     */
    public function testConstruct()
    {
        $id = 'room-123';
        $ownerUserId = 'user-456';
        $name = 'Test Room';
        $purpose = 'A room for testing';

        $room = new Room($id, $ownerUserId, $name, $purpose);

        $this->assertSame($id, $room->id);
        $this->assertSame($ownerUserId, $room->owner_user_id);
        $this->assertSame($name, $room->name);
        $this->assertSame($purpose, $room->purpose);
    }

    /**
     * @covers \Bristolian\Model\Room
     */
    public function testGetters()
    {
        $room = new Room('room-id', 'owner-id', 'Room Name', 'Room Purpose');

        $this->assertSame('room-id', $room->getRoomId());
        $this->assertSame('owner-id', $room->getOwnerUserId());
        $this->assertSame('Room Name', $room->getName());
        $this->assertSame('Room Purpose', $room->getPurpose());
    }

    /**
     * @covers \Bristolian\Model\Room
     */
    public function testToArray()
    {
        $room = new Room('id', 'owner', 'name', 'purpose');
        $array = $room->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('name', $array);
    }
}

