<?php

namespace Bristolian\Repo\UserRepo;

use Bristolian\Model\User;
use Bristolian\Model\UserDocument;
use Bristolian\Types\DocumentType;

use Bristolian\Types\UserList;

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
