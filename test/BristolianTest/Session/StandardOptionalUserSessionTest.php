<?php

namespace BristolianTest\Session;

use BristolianTest\BaseTestCase;
use Bristolian\Session\StandardOptionalUserSession;
use Bristolian\Session\AppSession;

/**
 * @covers \Bristolian\Session\StandardOptionalUserSession
 */
class StandardOptionalUserSessionTest extends BaseTestCase
{
    // Technically, I don't like mocks, but also, I am lazy.
    public function testConstructorStoresAppSession(): void
    {
        $appSessionMock = $this->createMock(AppSession::class);
        $session = new StandardOptionalUserSession($appSessionMock);
        $this->assertSame($appSessionMock, $session->getAppSession());
    }

    public function testGetAppSessionReturnsAppSession(): void
    {
        $appSessionMock = $this->createMock(AppSession::class);
        $session = new StandardOptionalUserSession($appSessionMock);
        $this->assertInstanceOf(AppSession::class, $session->getAppSession());
    }

    public function testGetAppSessionReturnsNull(): void
    {
        $session = new StandardOptionalUserSession(null);
        $this->assertNull($session->getAppSession());
    }
}
