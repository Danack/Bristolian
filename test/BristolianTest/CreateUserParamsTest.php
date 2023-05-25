<?php

declare(strict_types = 1);

namespace OsfTest\Params;

use Bristolian\DataType\CreateUserParams;
use VarMap\ArrayVarMap;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class CreateUserParamsTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\DataType\CreateUserParams
     */
    public function testWorks()
    {
        $username = 'Johnathan@example.com';
        $password = 'mynameismypassport';

        $createAdminParams = CreateUserParams::createFromVarMap(new ArrayVarMap([
            'username' => $username,
            'password' => $password
        ]));

        $this->assertSame($username, $createAdminParams->getEmailaddress());
        $this->assertSame($password, $createAdminParams->getPassword());
    }
}
