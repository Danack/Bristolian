<?php

namespace BristolianTest\Session;

use Bristolian\Session\FakeUserSession;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Session\FakeUserSession
 * @group wip
 */
class FakeUserSessionTest extends BaseTestCase
{
    public function testWorks()
    {
        $isLoggedIn = false;
        $userId = "12345";
        $username = "John";


        $session = new FakeUserSession(
            $isLoggedIn,
            $userId,
            $username,
        );

        $this->assertSame($isLoggedIn, $session->isLoggedIn());
        $this->assertSame($userId, $session->getUserId());
        $this->assertSame($username, $session->getUsername());
    }
}
