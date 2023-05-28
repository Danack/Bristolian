<?php

declare(strict_types = 1);

namespace BristolianTest\DataType;

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
        $email_address = 'Johnathan@example.com';
        $password = 'mynameismypassport';

        $createAdminParams = CreateUserParams::createFromVarMap(new ArrayVarMap([
            'email_address' => $email_address,
            'password' => $password
        ]));

        $this->assertSame($email_address, $createAdminParams->getEmailaddress());
        $this->assertSame($password, $createAdminParams->getPassword());
    }
}
