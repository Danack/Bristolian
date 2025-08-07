<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\AdminRepo;

use Bristolian\Parameters\CreateUserParams;
use BristolianTest\Repo\TestPlaceholders;
use BristolianTest\BaseTestCase;
use Bristolian\Repo\AdminRepo\PdoAdminRepo;

/**
 * @coversNothing
 */
class PdoAdminRepoTest extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * @covers \Bristolian\Repo\AdminRepo\PdoAdminRepo
     * @group db
     */
    public function testWorks(): void
    {
        $username = 'username' . time() . '_' . random_int(1000, 9999) . "@example.com";
        $password = 'password_' . time() . '_' . random_int(1000, 9999);

        $createAdminUserParams = CreateUserParams::createFromArray([
            'email_address' => $username,
            'password' => $password
        ]);

        $pdo_admin_repo = $this->injector->make(PdoAdminRepo::class);
        $adminUser = $pdo_admin_repo->addUser($createAdminUserParams);

        $adminUserFromDB = $pdo_admin_repo->getAdminUser($username, $password);

        $this->assertSame(
            $createAdminUserParams->getEmailaddress(),
            $adminUserFromDB->getEmailAddress()
        );

        $adminUserId = $pdo_admin_repo->getAdminUserId($username);
        $this->assertSame(
            $adminUserFromDB->getUserId(),
            $adminUserId
        );

        $nullUserId = $pdo_admin_repo->getAdminUserId('non_existent_user');
        $this->assertNull($nullUserId);

        $nullUserFromDB = $pdo_admin_repo->getAdminUser('non_existent_user', 'foo');
        $this->assertNull($nullUserFromDB);

        $nullUserFromDB = $pdo_admin_repo->getAdminUser($username, 'bad_password');
        $this->assertNull($nullUserFromDB);
    }
}
