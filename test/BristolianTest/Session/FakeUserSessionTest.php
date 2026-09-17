<?php

namespace BristolianTest\Session;

use Bristolian\Session\FakeUserSession;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Session\FakeUserSession::class, '__construct')]
#[CoversMethod(\Bristolian\Session\FakeUserSession::class, 'getUserId')]
#[CoversMethod(\Bristolian\Session\FakeUserSession::class, 'getUsername')]
#[CoversMethod(\Bristolian\Session\FakeUserSession::class, 'isLoggedIn')]

class FakeUserSessionTest extends BaseTestCase
{
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
