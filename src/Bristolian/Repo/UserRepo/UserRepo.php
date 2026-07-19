<?php

namespace Bristolian\Repo\UserRepo;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use Bristolian\Database\user;
use Bristolian\Database\user_ownership;
use Bristolian\Model\Generated\UserOwnership;

interface UserRepo
{
    const TYPE_SYSTEM = 'SYSTEM';

    const TYPE_ROOM_USER = 'ROOM_USER';

    #[ReadsTable(user_ownership::class)]
    #[WritesTable(user::class)]
    #[WritesTable(user_ownership::class)]
    public function ensureSystemUserExists(): UserOwnership;

    #[ReadsTable(user_ownership::class)]
    #[WritesTable(user::class)]
    #[WritesTable(user_ownership::class)]
    public function ensureRoomUserOwnershipExistsForRoom(string $room_id): UserOwnership;

    #[ReadsTable(user_ownership::class)]
    public function getSystemUser(): UserOwnership;

    #[ReadsTable(user_ownership::class)]
    public function getRoomUserForRoom(string $room_id): UserOwnership;
}
