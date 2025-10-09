<?php

namespace BristolianTest\Model;

use BristolianTest\BaseTestCase;
use Bristolian\Model\User;

/**
 * @coversNothing
 */
class UserTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\User
     */
    public function testConstruct()
    {
        $username = 'testuser';
        $user = new User($username);

        $this->assertSame($username, $user->username);
    }
}

