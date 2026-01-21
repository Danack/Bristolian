<?php

declare(strict_types = 1);

namespace Bristolian\Repo\UserRepo;

use Bristolian\Model\Generated\User;

/**
 * Fake implementation of UserRepo for testing.
 */
class FakeUserRepo implements UserRepo
{
    /**
     * @var User[]
     */
    private array $users = [];

    /**
     * @param User[] $initialUsers
     * Note: User model doesn't have username property, so initial users cannot be indexed by username.
     * This constructor stores users by their id, but findUser() still expects username.
     */
    public function __construct(array $initialUsers = [])
    {
        // Store users by their id for now
        // Note: findUser() uses username which User doesn't have - this needs to be fixed
        foreach ($initialUsers as $user) {
            $this->users[$user->id] = $user;
        }
    }

    /**
     * @return User[]
     */
    public function getUsers(): array
    {
        return array_values($this->users);
    }

    public function findUser(string $username): User|null
    {
        return $this->users[$username] ?? null;
    }
}