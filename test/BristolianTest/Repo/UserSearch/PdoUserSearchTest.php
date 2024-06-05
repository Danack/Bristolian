<?php

namespace BristolianTest\Repo\UserSearch;

use Bristolian\DataType\CreateUserParams;
use Bristolian\Repo\AdminRepo\PdoAdminRepo;
use Bristolian\Repo\DbInfo\PdoDbInfo;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;
use Bristolian\Repo\UserSearch\PdoUserSearch;
use Bristolian\Repo\UserSearch\UserSearch;

/**
 * @coversNothing
 */
class PdoUserSearchTest extends BaseTestCase
{
    use TestPlaceholders;

    public function testWorks()
    {
        $prefix = 'username_testWorks' . time();

        $username = $prefix . '_' . random_int(1000, 9999) . "@example.com";
        $password = 'password_' . time() . '_' . random_int(1000, 9999);

        $createAdminUserParams = CreateUserParams::createFromArray([
            'email_address' => $username,
            'password' => $password
        ]);

        $pdo_admin_repo = $this->injector->make(PdoAdminRepo::class);
        $adminUser = $pdo_admin_repo->addUser($createAdminUserParams);

        $user_search_repo = $this->injector->make(PdoUserSearch::class);

        $results = $user_search_repo->searchUsernamesByPrefix('non-existent');
        $this->assertEmpty($results);

        $results = $user_search_repo->searchUsernamesByPrefix($prefix);
        $this->assertCount(1, $results);

        $results = $user_search_repo->searchUsernamesByPrefix('username');
        $count = count($results);

        $this->assertLessThanOrEqual(
            UserSearch::MAX_SEARCH_RESULTS,
            $count
        );
    }
}
