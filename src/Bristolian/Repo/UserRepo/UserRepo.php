<?php

namespace Bristolian\Repo\UserRepo;

use User;

interface UserRepo
{
    /**
     * @return \User[]
     */
    public function getUsers(): array;

    public function findUser(string $username): User|null;
}
