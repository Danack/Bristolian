<?php

declare(strict_types=1);

namespace BristolianTest\Session;

use Bristolian\Model\Types\AdminUser;
use Bristolian\Session\AppSession;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class AppSessionTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Session\AppSession::__construct
     * @covers \Bristolian\Session\AppSession::isLoggedIn
     */
    public function test_isLoggedIn_returns_true(): void
    {
        $rawSession = new FakeAsmSession();
        $session = new AppSession($rawSession);

        $this->assertTrue($session->isLoggedIn());
    }

    /**
     * @covers \Bristolian\Session\AppSession::setUserId
     * @covers \Bristolian\Session\AppSession::getUserId
     */
    public function test_setUserId_and_getUserId(): void
    {
        $rawSession = new FakeAsmSession();
        $session = new AppSession($rawSession);

        $session->setUserId('user-abc');
        $this->assertSame('user-abc', $session->getUserId());
    }

    /**
     * @covers \Bristolian\Session\AppSession::setUsername
     * @covers \Bristolian\Session\AppSession::getUsername
     */
    public function test_setUsername_and_getUsername(): void
    {
        $rawSession = new FakeAsmSession();
        $session = new AppSession($rawSession);

        $session->setUsername('alice@example.com');
        $this->assertSame('alice@example.com', $session->getUsername());
    }

    /**
     * @covers \Bristolian\Session\AppSession::setLoggedIn
     */
    public function test_setLoggedIn(): void
    {
        $rawSession = new FakeAsmSession();
        $session = new AppSession($rawSession);

        $session->setLoggedIn(true);
        $this->assertTrue($rawSession->get(AppSession::LOGGED_IN));
    }

    /**
     * @covers \Bristolian\Session\AppSession::createSessionForUser
     */
    public function test_createSessionForUser(): void
    {
        $rawSession = new FakeAsmSession();
        $user = AdminUser::new('user-id-1', 'admin@example.com', 'hashed_pw');

        $appSession = AppSession::createSessionForUser($rawSession, $user);

        $this->assertTrue($appSession->isLoggedIn());
        $this->assertSame('user-id-1', $appSession->getUserId());
        $this->assertSame('admin@example.com', $appSession->getUsername());
    }
}
