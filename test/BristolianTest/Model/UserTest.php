<?php

namespace BristolianTest\Model;

use BristolianTest\BaseTestCase;
use User;

/**
 * @coversNothing
 */
class UserTest extends BaseTestCase
{
    /**
     * @covers \User
     */
    public function testConstruct()
    {
        $username = 'testuser';
        $user = new User($username);

        $this->assertSame($username, $user->username);
    }
}

