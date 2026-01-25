<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\AdminRepo;

use Bristolian\Parameters\CreateUserParams;
use Bristolian\Repo\AdminRepo\AdminRepo;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;

/**
 * Abstract test class for AdminRepo implementations.
 * @internal
 */
abstract class AdminRepoFixture extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * Get a test instance of the AdminRepo implementation.
     *
     * @return AdminRepo
     */
    abstract public function getTestInstance(): AdminRepo;


    /**
     * @covers \Bristolian\Repo\AdminRepo\AdminRepo::addUser
     */
    public function test_addUser(): void
    {
        $repo = $this->getTestInstance();

        $username = 'username' . time() . '_' . random_int(1000, 9999) . "@example.com";
        $password = 'password_' . time() . '_' . random_int(1000, 9999);

        $createAdminUserParams = CreateUserParams::createFromArray([
            'email_address' => $username,
            'password' => $password
        ]);

        $adminUser = $repo->addUser($createAdminUserParams);

        $this->assertSame(
            $createAdminUserParams->getEmailaddress(),
            $adminUser->getEmailAddress()
        );
    }


    /**
     * @covers \Bristolian\Repo\AdminRepo\AdminRepo::getAdminUser
     * @covers \Bristolian\Repo\AdminRepo\AdminRepo::addUser
     */
    public function test_getAdminUser_returns_user_with_correct_credentials(): void
    {
        $repo = $this->getTestInstance();

        $username = 'username' . time() . '_' . random_int(1000, 9999) . "@example.com";
        $password = 'password_' . time() . '_' . random_int(1000, 9999);

        $createAdminUserParams = CreateUserParams::createFromArray([
            'email_address' => $username,
            'password' => $password
        ]);

        $repo->addUser($createAdminUserParams);

        $adminUserFromDB = $repo->getAdminUser($username, $password);
        $this->assertNotNull($adminUserFromDB);
        $this->assertSame(
            $createAdminUserParams->getEmailaddress(),
            $adminUserFromDB->getEmailAddress()
        );
    }


    /**
     * @covers \Bristolian\Repo\AdminRepo\AdminRepo::getAdminUser
     */
    public function test_getAdminUser_returns_null_for_nonexistent_user(): void
    {
        $repo = $this->getTestInstance();

        $nonExistentUser = $repo->getAdminUser('nonexistent_username', 'foo');
        $this->assertNull($nonExistentUser);
    }


    /**
     * @covers \Bristolian\Repo\AdminRepo\AdminRepo::getAdminUser
     */
    public function test_getAdminUser_returns_null_for_wrong_password(): void
    {
        $repo = $this->getTestInstance();

        $username = 'username' . time() . '_' . random_int(1000, 9999) . "@example.com";
        $password = 'password_' . time() . '_' . random_int(1000, 9999);

        $createAdminUserParams = CreateUserParams::createFromArray([
            'email_address' => $username,
            'password' => $password
        ]);

        $repo->addUser($createAdminUserParams);

        $wrongPasswordUser = $repo->getAdminUser($username, 'wrong_password');
        $this->assertNull($wrongPasswordUser);
    }


    /**
     * @covers \Bristolian\Repo\AdminRepo\AdminRepo::getAdminUserId
     */
    public function test_getAdminUserId_returns_user_id_for_existing_user(): void
    {
        $repo = $this->getTestInstance();

        $username = 'username' . time() . '_' . random_int(1000, 9999) . "@example.com";
        $password = 'password_' . time() . '_' . random_int(1000, 9999);

        $createAdminUserParams = CreateUserParams::createFromArray([
            'email_address' => $username,
            'password' => $password
        ]);

        $adminUser = $repo->addUser($createAdminUserParams);
        $adminUserFromDB = $repo->getAdminUser($username, $password);

        $adminUserId = $repo->getAdminUserId($username);
        $this->assertNotNull($adminUserId);
        $this->assertSame(
            $adminUserFromDB->getUserId(),
            $adminUserId
        );
    }


    /**
     * @covers \Bristolian\Repo\AdminRepo\AdminRepo::getAdminUserId
     */
    public function test_getAdminUserId_returns_null_for_nonexistent_user(): void
    {
        $repo = $this->getTestInstance();

        $nullUserId = $repo->getAdminUserId('nonexistent_username');
        $this->assertNull($nullUserId);
    }
}
