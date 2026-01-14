<?php

namespace Bristolian\Repo\UserRepo;

use Bristolian\Types\UserList;
use User;

class HardcodedUserRepo implements UserRepo
{
    public function getUsers(): array
    {
        return [
            new User(UserList::sid->value),
        ];
    }

    public function findUser(string $username): User|null
    {
        if ($username !== UserList::sid->value) {
            return null;
        }

        return new User(UserList::sid->value);
    }
}
