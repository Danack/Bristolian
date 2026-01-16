<?php

namespace Bristolian\Repo\UserRepo;

use Bristolian\Model\Generated\User;

interface UserRepo
{
    /**
     * @return User[]
     */
    public function getUsers(): array;

    public function findUser(string $username): User|null;
}
