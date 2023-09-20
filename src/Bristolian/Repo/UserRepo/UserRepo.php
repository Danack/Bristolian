<?php

namespace Bristolian\Repo\UserRepo;

use Bristolian\Model\User;

interface UserRepo
{
    /**
     * @return \Bristolian\Model\User[]
     */
    public function getUsers(): array;

    public function findUser(string $username): User|null;
}
