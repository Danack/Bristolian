<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomRepo;

use Bristolian\Repo\RoomRepo\FakeRoomRepo;
use Bristolian\Repo\RoomRepo\RoomRepo;

/**
 * @group standard_repo
 * @coversNothing
 */
class FakeRoomRepoTest extends RoomRepoFixture
{
    public function getTestInstance(): RoomRepo
    {
        return new FakeRoomRepo();
    }

    protected function getValidUserId(): string
    {
        return 'user_123';
    }

    /**
     * @covers \Bristolian\Repo\RoomRepo\FakeRoomRepo::updateRoomNameAndPurpose
     */
    public function test_updateRoomNameAndPurpose_with_nonexistent_room_id_does_nothing(): void
    {
        $repo = new FakeRoomRepo();
        $createdRoom = $repo->createRoom(
            $this->getValidUserId(),
            'Existing Room',
            'Existing purpose'
        );

        $repo->updateRoomNameAndPurpose('nonexistent-room-id', 'Updated name', 'Updated purpose');

        $retrievedRoom = $repo->getRoomById($createdRoom->id);
        $this->assertNotNull($retrievedRoom);
        $this->assertSame('Existing Room', $retrievedRoom->name);
        $this->assertSame('Existing purpose', $retrievedRoom->purpose);
    }
}
