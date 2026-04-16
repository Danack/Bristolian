<?php

declare(strict_types=1);

namespace BristolianTest\Repo\UserRepo;

use Bristolian\Repo\RoomRepo\PdoRoomRepo;
use Bristolian\Repo\UserRepo\PdoUserRepo;
use Bristolian\Repo\UserRepo\UserRepo;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;
use PDO;

/**
 * @group db
 * @coversNothing
 */
final class PdoUserRepoTest extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * @covers \Bristolian\Repo\UserRepo\PdoUserRepo::__construct
     * @covers \Bristolian\Repo\UserRepo\PdoUserRepo::ensureRoomUserOwnershipExistsForRoom
     * @covers \Bristolian\Repo\UserRepo\PdoUserRepo::getRoomUserForRoom
     */
    public function test_ensureRoomUserOwnershipExistsForRoom_is_idempotent_and_getRoomUserForRoom_matches(): void
    {
        $adminUser = $this->createTestAdminUser();
        $roomRepo = $this->injector->make(PdoRoomRepo::class);
        $room = $roomRepo->createRoom(
            $adminUser->getUserId(),
            'PdoUserRepo test room ' . random_int(1_000_000, 9_999_999),
            'Coverage test room purpose'
        );

        $repo = $this->injector->make(PdoUserRepo::class);

        $first = $repo->ensureRoomUserOwnershipExistsForRoom($room->id);
        $second = $repo->ensureRoomUserOwnershipExistsForRoom($room->id);

        self::assertSame($first->user_id, $second->user_id);
        self::assertSame(UserRepo::TYPE_ROOM_USER, $first->type);
        self::assertSame($room->id, $first->room_id);

        $fromGet = $repo->getRoomUserForRoom($room->id);
        self::assertSame($first->user_id, $fromGet->user_id);
        self::assertSame(UserRepo::TYPE_ROOM_USER, $fromGet->type);
        self::assertSame($room->id, $fromGet->room_id);
    }

    /**
     * Uses raw PDO deletes so test DBs that already have a SYSTEM row still exercise the insert path.
     *
     * @covers \Bristolian\Repo\UserRepo\PdoUserRepo::ensureSystemUserExists
     */
    public function test_ensureSystemUserExists_inserts_after_system_row_removed(): void
    {
        $pdo = $this->injector->make(PDO::class);
        $repo = $this->injector->make(PdoUserRepo::class);

        $before = $repo->ensureSystemUserExists();
        $previousUserId = $before->user_id;

        $statement = $pdo->prepare('DELETE FROM user_ownership WHERE type = :type');
        $statement->execute([':type' => UserRepo::TYPE_SYSTEM]);

        $after = $repo->ensureSystemUserExists();

        self::assertNotSame($previousUserId, $after->user_id);
        self::assertSame(UserRepo::TYPE_SYSTEM, $after->type);
        self::assertNull($after->room_id);
    }

    /**
     * @covers \Bristolian\Repo\UserRepo\PdoUserRepo::ensureSystemUserExists
     * @covers \Bristolian\Repo\UserRepo\PdoUserRepo::getSystemUser
     */
    public function test_ensureSystemUserExists_is_idempotent_and_getSystemUser_matches(): void
    {
        $repo = $this->injector->make(PdoUserRepo::class);

        $first = $repo->ensureSystemUserExists();
        $second = $repo->ensureSystemUserExists();

        self::assertSame($first->user_id, $second->user_id);
        self::assertSame(UserRepo::TYPE_SYSTEM, $first->type);
        self::assertNull($first->room_id);

        $fromGet = $repo->getSystemUser();
        self::assertSame($first->user_id, $fromGet->user_id);
        self::assertSame(UserRepo::TYPE_SYSTEM, $fromGet->type);
        self::assertNull($fromGet->room_id);
    }
}
