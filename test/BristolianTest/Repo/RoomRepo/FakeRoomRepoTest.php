<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomRepo;

use Bristolian\Repo\RoomRepo\FakeRoomRepo;
use Bristolian\Repo\RoomRepo\RoomRepo;
use PHPUnit\Framework\Attributes\CoversMethod;

/**
 * @group standard_repo
 */

#[CoversMethod(\Bristolian\Repo\RoomRepo\FakeRoomRepo::class, 'createRoom')]
#[CoversMethod(\Bristolian\Repo\RoomRepo\FakeRoomRepo::class, 'getAllRooms')]
#[CoversMethod(\Bristolian\Repo\RoomRepo\FakeRoomRepo::class, 'getLatestRoomsCreated')]
#[CoversMethod(\Bristolian\Repo\RoomRepo\FakeRoomRepo::class, 'updateRoomNameAndPurpose')]

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

    public function test_list_methods_honour_explicit_limit(): void
    {
        $repo = new FakeRoomRepo();
        $userId = $this->getValidUserId();
        $first = $repo->createRoom($userId, 'First', 'Purpose');
        $second = $repo->createRoom($userId, 'Second', 'Purpose');
        $third = $repo->createRoom($userId, 'Third', 'Purpose');

        $oldest = $repo->getAllRooms(1);
        $this->assertCount(1, $oldest);
        $this->assertSame($first->id, $oldest[0]->id);

        $newest = $repo->getLatestRoomsCreated(1);
        $this->assertCount(1, $newest);
        $this->assertSame($third->id, $newest[0]->id);
        $this->assertNotSame($second->id, $newest[0]->id);
    }
}
