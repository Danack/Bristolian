<?php

declare(strict_types=1);

namespace BristolianTest\Session;

use Asm\Encrypter\NullEncrypter;
use Asm\Encrypter\NullEncrypterFactory;
use Asm\SessionConfig;
use Asm\SessionManager;
use Bristolian\Session\FakeAsmDriver;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class FakeAsmDriverTest extends BaseTestCase
{
    private FakeAsmDriver $driver;
    private NullEncrypter $encrypter;
    private SessionManager $sessionManager;

    public function setup(): void
    {
        parent::setup();
        $this->driver = new FakeAsmDriver();
        $this->encrypter = new NullEncrypter();
        $config = new SessionConfig('test_session', 3600);
        $this->sessionManager = new SessionManager(
            $config,
            $this->driver,
            null,
            new NullEncrypterFactory()
        );
    }

    /**
     * @covers \Bristolian\Session\FakeAsmDriver::__construct
     */
    public function test_construct(): void
    {
        $driver = new FakeAsmDriver([['Set-Cookie', 'test=abc']]);
        $this->assertInstanceOf(FakeAsmDriver::class, $driver);
    }

    /**
     * @covers \Bristolian\Session\FakeAsmDriver::openSessionByID
     */
    public function test_openSessionByID_returns_null_when_no_session(): void
    {
        $result = $this->driver->openSessionByID(
            'nonexistent',
            $this->encrypter,
            $this->sessionManager,
            null
        );
        $this->assertNull($result);
    }

    /**
     * @covers \Bristolian\Session\FakeAsmDriver::openSessionByID
     * @covers \Bristolian\Session\FakeAsmDriver::addSession
     */
    public function test_openSessionByID_returns_session_after_addSession(): void
    {
        $session = new FakeAsmSession('sess-123');
        $this->driver->addSession($session);

        $result = $this->driver->openSessionByID(
            'sess-123',
            $this->encrypter,
            $this->sessionManager,
            null
        );
        $this->assertSame($session, $result);
    }

    /**
     * @covers \Bristolian\Session\FakeAsmDriver::createSession
     */
    public function test_createSession_returns_new_session(): void
    {
        $session = $this->driver->createSession(
            $this->encrypter,
            $this->sessionManager
        );
        $this->assertInstanceOf(FakeAsmSession::class, $session);
        $this->assertSame('session-1', $session->getSessionId());
    }

    /**
     * @covers \Bristolian\Session\FakeAsmDriver::createSession
     */
    public function test_createSession_increments_id(): void
    {
        $first = $this->driver->createSession($this->encrypter, $this->sessionManager);
        $second = $this->driver->createSession($this->encrypter, $this->sessionManager);

        $this->assertSame('session-1', $first->getSessionId());
        $this->assertSame('session-2', $second->getSessionId());
    }

    /**
     * @covers \Bristolian\Session\FakeAsmDriver::deleteSessionByID
     */
    public function test_deleteSessionByID_removes_session(): void
    {
        $session = new FakeAsmSession('sess-to-delete');
        $this->driver->addSession($session);

        $this->driver->deleteSessionByID('sess-to-delete');

        $result = $this->driver->openSessionByID(
            'sess-to-delete',
            $this->encrypter,
            $this->sessionManager,
            null
        );
        $this->assertNull($result);
    }

    /**
     * @covers \Bristolian\Session\FakeAsmDriver::forceReleaseLockByID
     */
    public function test_forceReleaseLockByID_does_not_throw(): void
    {
        $this->driver->forceReleaseLockByID('any-id');
        $this->assertTrue(true);
    }
}
