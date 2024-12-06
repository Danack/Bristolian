<?php

namespace Bristolian\CliController;

use Bristolian\Repo\AdminRepo\AdminRepo;
use Bristolian\Repo\RoomRepo\RoomRepo;

class Rooms
{
    public function createFromCli(
        AdminRepo $adminRepo,
        RoomRepo $roomRepo,
        string $name,
        string $purpose
    ): void {

        $user_id = $adminRepo->getAdminUserId(getAdminEmailAddress());
        if ($user_id === null) {
            echo "Failed to find admin user";
            exit(-1);
        }

        $roomRepo->createRoom(
            $user_id,
            $name,
            $purpose
        );
    }
}
