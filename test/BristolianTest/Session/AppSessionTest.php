<?php

declare(strict_types=1);

namespace BristolianTest\Session;

use Bristolian\Model\Types\AdminUser;
use Bristolian\Session\AppSession;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Session\AppSession::class, '__construct')]
#[CoversMethod(\Bristolian\Session\AppSession::class, 'createSessionForUser')]
#[CoversMethod(\Bristolian\Session\AppSession::class, 'getUserId')]
#[CoversMethod(\Bristolian\Session\AppSession::class, 'getUsername')]
#[CoversMethod(\Bristolian\Session\AppSession::class, 'isLoggedIn')]
#[CoversMethod(\Bristolian\Session\AppSession::class, 'setLoggedIn')]
#[CoversMethod(\Bristolian\Session\AppSession::class, 'setUserId')]
#[CoversMethod(\Bristolian\Session\AppSession::class, 'setUsername')]

class AppSessionTest extends BaseTestCase
{
    public function test_isLoggedIn_returns_true(): void
    {
        $rawSession = new FakeAsmSession();
        $session = new AppSession($rawSession);

        $this->assertTrue($session->isLoggedIn());
    }

    public function test_setUserId_and_getUserId(): void
    {
        $rawSession = new FakeAsmSession();
        $session = new AppSession($rawSession);

        $session->setUserId('user-abc');
        $this->assertSame('user-abc', $session->getUserId());
    }

    public function test_setUsername_and_getUsername(): void
    {
        $rawSession = new FakeAsmSession();
        $session = new AppSession($rawSession);

        $session->setUsername('alice@example.com');
        $this->assertSame('alice@example.com', $session->getUsername());
    }

    public function test_setLoggedIn(): void
    {
        $rawSession = new FakeAsmSession();
        $session = new AppSession($rawSession);

        $session->setLoggedIn(true);
        $this->assertTrue($rawSession->get(AppSession::LOGGED_IN));
    }

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
