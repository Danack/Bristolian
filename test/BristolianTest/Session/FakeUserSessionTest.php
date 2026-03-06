<?php

namespace BristolianTest\Session;

use Bristolian\Session\FakeUserSession;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class FakeUserSessionTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Session\FakeUserSession::__construct
     * @covers \Bristolian\Session\FakeUserSession::isLoggedIn
     * @covers \Bristolian\Session\FakeUserSession::getUserId
     * @covers \Bristolian\Session\FakeUserSession::getUsername
     */
    public function test_all_getters(): void
    {
        $session = new FakeUserSession(
            false,
            '12345',
            'John',
        );

        $this->assertFalse($session->isLoggedIn());
        $this->assertSame('12345', $session->getUserId());
        $this->assertSame('John', $session->getUsername());
    }
}
