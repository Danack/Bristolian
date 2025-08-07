<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\AdminRepo;

use Bristolian\Parameters\CreateUserParams;
use BristolianTest\Repo\TestPlaceholders;
use BristolianTest\BaseTestCase;
use Bristolian\Repo\AdminRepo\FakeAdminRepo;

/**
 * @coversNothing
 */
class FakeAdminRepoTest extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * @covers \Bristolian\Repo\AdminRepo\FakeAdminRepo
     */
    public function testWorks(): void
    {
        $username = 'username' . time() . '_' . random_int(1000, 9999) . "@example.com";
        $password = 'password_' . time() . '_' . random_int(1000, 9999);

        $createAdminUserParams = CreateUserParams::createFromArray([
            'email_address' => $username,
            'password' => $password
        ]);

        $admin_repo = new FakeAdminRepo([]);
        $adminUser = $admin_repo->addUser($createAdminUserParams);

        $adminUserFromDB = $admin_repo->getAdminUser($username, $password);
        $this->assertSame(
            $createAdminUserParams->getEmailaddress(),
            $adminUserFromDB->getEmailAddress()
        );

        $nonExistentUser = $admin_repo->getAdminUser(
            'nonexistent_username', 'foo'
        );
        $this->assertNull($nonExistentUser);



        $adminUserId = $admin_repo->getAdminUserId($username);
        $this->assertSame(
            $adminUserFromDB->getEmailAddress(),
            $adminUserId
        );

        $nullUserId = $admin_repo->getAdminUserId('nonexistent_username');
        $this->assertNull($nullUserId);
    }
}
