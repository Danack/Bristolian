<?php

namespace Bristolian\Repo\UserRepo;

use Bristolian\Model\Generated\UserOwnership;

interface UserRepo
{
    const TYPE_SYSTEM = 'SYSTEM';

    const TYPE_ROOM_USER = 'ROOM_USER';

    public function ensureSystemUserExists(): UserOwnership;

    public function ensureRoomUserOwnershipExistsForRoom(string $room_id): UserOwnership;

    public function getSystemUser(): UserOwnership;

    public function getRoomUserForRoom(string $room_id): UserOwnership;
}
