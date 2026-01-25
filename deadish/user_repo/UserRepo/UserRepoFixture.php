<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\UserRepo;

use Bristolian\Model\Generated\User;
use Bristolian\Repo\UserRepo\UserRepo;
use BristolianTest\BaseTestCase;

/**
 * Abstract test class for UserRepo implementations.
 */
abstract class UserRepoFixture extends BaseTestCase
{
    /**
     * Get a test instance of the UserRepo implementation.
     *
     * @return UserRepo
     */
    abstract public function getTestInstance(): UserRepo;

    /**
     * @covers \Bristolian\Repo\UserRepo\UserRepo::getUsers
     */
    public function test_getUsers_returns_array(): void
    {
        $repo = $this->getTestInstance();

        $users = $repo->getUsers();

        // Note: PDO tests may have existing data, so we don't assert empty
        foreach ($users as $user) {
            $this->assertInstanceOf(User::class, $user);
        }
    }

    /**
     * @covers \Bristolian\Repo\UserRepo\UserRepo::findUser
     */
    public function test_findUser_returns_null_for_nonexistent_user(): void
    {
        $repo = $this->getTestInstance();

        $user = $repo->findUser('nonexistent');

        $this->assertNull($user);
    }
}
