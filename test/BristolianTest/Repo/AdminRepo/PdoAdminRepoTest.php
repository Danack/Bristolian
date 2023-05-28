<?php

declare(strict_types = 1);

namespace BristolianTest\Params;

use Bristolian\DataType\CreateUserParams;
use BristolianTest\Repo\Testing;
use VarMap\ArrayVarMap;
use BristolianTest\BaseTestCase;
use Bristolian\Repo\AdminRepo\PdoAdminRepo;

/**
 * @coversNothing
 */
class PdoAdminRepoTest extends BaseTestCase
{
    use Testing;

    /**
     * @covers \Bristolian\Repo\AdminRepo\PdoAdminRepo
     */
    public function testWorks()
    {
        $email_address = 'Johnathan@example.com';
        $password = 'mynameismypassport';

        $createAdminParams = CreateUserParams::createFromVarMap(new ArrayVarMap([
            'email_address' => $email_address,
            'password' => $password
        ]));

//        $this->assertSame($username, $createAdminParams->getEmailaddress());
//        $this->assertSame($password, $createAdminParams->getPassword());

        $pdo_admin_repo = $this->injector->make(PdoAdminRepo::class);

        $result = $pdo_admin_repo->addUser($createAdminParams);
    }
}
