<?php

declare(strict_types=1);

namespace BristolianTest\Session;

use Asm\Encrypter\NullEncrypterFactory;
use Asm\SessionConfig;
use Asm\SessionManager;
use Bristolian\Exception\BristolianException;
use Bristolian\Session\AppSession;
use Bristolian\Session\AppSessionManager;
use Bristolian\Session\FakeAsmDriver;
use BristolianTest\BaseTestCase;
use Laminas\Diactoros\ServerRequest;

/**
 * @coversNothing
 */
class AppSessionManagerTest extends BaseTestCase
{
    private function createSessionManager(FakeAsmDriver $driver = null): SessionManager
    {
        $config = new SessionConfig('test_session', 3600);
        $driver = $driver ?? new FakeAsmDriver([
            ['Set-Cookie', 'test_session=abc; path=/; httpOnly'],
        ]);

        return new SessionManager($config, $driver, null, new NullEncrypterFactory());
    }

    /**
     * @param array<string, string> $cookies
     */
    private function createRequest(array $cookies = []): ServerRequest
    {
        return (new ServerRequest())->withCookieParams($cookies);
    }

    /**
     * @covers \Bristolian\Session\AppSessionManager::__construct
     */
    public function test_construct(): void
    {
        $manager = new AppSessionManager($this->createSessionManager());
        $this->assertInstanceOf(AppSessionManager::class, $manager);
    }

    /**
     * @covers \Bristolian\Session\AppSessionManager::initialize
     */
    public function test_initialize_sets_request(): void
    {
        $manager = new AppSessionManager($this->createSessionManager());
        $manager->initialize($this->createRequest());

        $session = $manager->getCurrentAppSession();
        $this->assertNull($session);
    }

    /**
     * @covers \Bristolian\Session\AppSessionManager::initialize
     */
    public function test_initialize_throws_when_called_twice(): void
    {
        $manager = new AppSessionManager($this->createSessionManager());
        $manager->initialize($this->createRequest());

        $this->expectException(BristolianException::class);
        $this->expectExceptionMessage('already initialized');

        $manager->initialize($this->createRequest());
    }

    /**
     * @covers \Bristolian\Session\AppSessionManager::getCurrentAppSession
     * @covers \Bristolian\Session\AppSessionManager::checkInitialised
     */
    public function test_getCurrentAppSession_throws_when_not_initialized(): void
    {
        $manager = new AppSessionManager($this->createSessionManager());

        $this->expectException(BristolianException::class);
        $this->expectExceptionMessage('not initialized');

        $manager->getCurrentAppSession();
    }

    /**
     * @covers \Bristolian\Session\AppSessionManager::getCurrentAppSession
     */
    public function test_getCurrentAppSession_returns_null_when_no_cookie(): void
    {
        $manager = new AppSessionManager($this->createSessionManager());
        $manager->initialize($this->createRequest());

        $this->assertNull($manager->getCurrentAppSession());
    }

    /**
     * @covers \Bristolian\Session\AppSessionManager::getCurrentAppSession
     */
    public function test_getCurrentAppSession_returns_session_from_cookie(): void
    {
        $driver = new FakeAsmDriver([
            ['Set-Cookie', 'test_session=existing-id; path=/; httpOnly'],
        ]);
        $existingSession = new FakeAsmSession('existing-id');
        $existingSession->set(AppSession::USER_ID, 'user-123');
        $driver->addSession($existingSession);

        $sessionManager = $this->createSessionManager($driver);
        $manager = new AppSessionManager($sessionManager);
        $manager->initialize($this->createRequest(['test_session' => 'existing-id']));

        $appSession = $manager->getCurrentAppSession();

        $this->assertNotNull($appSession);
        $this->assertSame('user-123', $appSession->getUserId());
    }

    /**
     * @covers \Bristolian\Session\AppSessionManager::getCurrentAppSession
     */
    public function test_getCurrentAppSession_returns_cached_session_on_second_call(): void
    {
        $driver = new FakeAsmDriver();
        $existingSession = new FakeAsmSession('sess-1');
        $existingSession->set(AppSession::USER_ID, 'user-abc');
        $driver->addSession($existingSession);

        $sessionManager = $this->createSessionManager($driver);
        $manager = new AppSessionManager($sessionManager);
        $manager->initialize($this->createRequest(['test_session' => 'sess-1']));

        $first = $manager->getCurrentAppSession();
        $second = $manager->getCurrentAppSession();

        $this->assertNotNull($first);
        $this->assertNotNull($second);
        $this->assertSame('user-abc', $first->getUserId());
        $this->assertSame('user-abc', $second->getUserId());
    }

    /**
     * @covers \Bristolian\Session\AppSessionManager::createRawSession
     */
    public function test_createRawSession_throws_when_not_initialized(): void
    {
        $manager = new AppSessionManager($this->createSessionManager());

        $this->expectException(BristolianException::class);
        $this->expectExceptionMessage('not initialized');

        $manager->createRawSession();
    }

    /**
     * @covers \Bristolian\Session\AppSessionManager::createRawSession
     */
    public function test_createRawSession_creates_new_session(): void
    {
        $manager = new AppSessionManager($this->createSessionManager());
        $manager->initialize($this->createRequest());

        $rawSession = $manager->createRawSession();

        $this->assertInstanceOf(\Asm\Session::class, $rawSession);
    }

    /**
     * @covers \Bristolian\Session\AppSessionManager::createRawSession
     */
    public function test_createRawSession_returns_existing_session_when_already_open(): void
    {
        $driver = new FakeAsmDriver();
        $existingSession = new FakeAsmSession('sess-existing');
        $driver->addSession($existingSession);

        $sessionManager = $this->createSessionManager($driver);
        $manager = new AppSessionManager($sessionManager);
        $manager->initialize($this->createRequest(['test_session' => 'sess-existing']));

        $manager->getCurrentAppSession();

        $rawSession = $manager->createRawSession();
        $this->assertSame('sess-existing', $rawSession->getSessionId());
    }

    /**
     * @covers \Bristolian\Session\AppSessionManager::deleteSession
     */
    public function test_deleteSession_throws_when_not_initialized(): void
    {
        $manager = new AppSessionManager($this->createSessionManager());

        $this->expectException(BristolianException::class);
        $this->expectExceptionMessage('not initialized');

        $manager->deleteSession();
    }

    /**
     * @covers \Bristolian\Session\AppSessionManager::deleteSession
     */
    public function test_deleteSession_with_existing_session(): void
    {
        $driver = new FakeAsmDriver();
        $existingSession = new FakeAsmSession('sess-del');
        $driver->addSession($existingSession);

        $sessionManager = $this->createSessionManager($driver);
        $manager = new AppSessionManager($sessionManager);
        $manager->initialize($this->createRequest(['test_session' => 'sess-del']));

        $manager->deleteSession();

        $this->assertTrue($existingSession->wasDeleted());
    }

    /**
     * @covers \Bristolian\Session\AppSessionManager::deleteSession
     */
    public function test_deleteSession_without_existing_session(): void
    {
        $manager = new AppSessionManager($this->createSessionManager());
        $manager->initialize($this->createRequest());

        $manager->deleteSession();

        $this->assertNull($manager->getCurrentAppSession());
    }

    /**
     * @covers \Bristolian\Session\AppSessionManager::renewSession
     */
    public function test_renewSession_with_active_session_returns_headers(): void
    {
        $driver = new FakeAsmDriver([
            ['Set-Cookie', 'test=abc'],
        ]);
        $existingSession = new FakeAsmSession('sess-renew', [
            ['Set-Cookie', 'test=abc'],
        ]);
        $driver->addSession($existingSession);

        $sessionManager = $this->createSessionManager($driver);
        $manager = new AppSessionManager($sessionManager);
        $manager->initialize($this->createRequest(['test_session' => 'sess-renew']));

        $headers = $manager->renewSession();

        $this->assertNotEmpty($headers);
        $this->assertTrue($existingSession->wasSaved());
    }

    /**
     * @covers \Bristolian\Session\AppSessionManager::renewSession
     */
    public function test_renewSession_without_session_returns_empty(): void
    {
        $manager = new AppSessionManager($this->createSessionManager());
        $manager->initialize($this->createRequest());

        $headers = $manager->renewSession();

        $this->assertSame([], $headers);
    }

    /**
     * @covers \Bristolian\Session\AppSessionManager::saveIfOpenedAndGetHeaders
     */
    public function test_saveIfOpenedAndGetHeaders_with_active_session(): void
    {
        $driver = new FakeAsmDriver();
        $existingSession = new FakeAsmSession('sess-save', [
            ['Set-Cookie', 'test=save'],
        ]);
        $driver->addSession($existingSession);

        $sessionManager = $this->createSessionManager($driver);
        $manager = new AppSessionManager($sessionManager);
        $manager->initialize($this->createRequest(['test_session' => 'sess-save']));

        $manager->getCurrentAppSession();

        $headers = $manager->saveIfOpenedAndGetHeaders();

        $this->assertNotEmpty($headers);
        $this->assertTrue($existingSession->wasSaved());
    }

    /**
     * @covers \Bristolian\Session\AppSessionManager::saveIfOpenedAndGetHeaders
     * @covers \Bristolian\Session\AppSessionManager::getRawSession
     */
    public function test_saveIfOpenedAndGetHeaders_without_session_returns_empty(): void
    {
        $manager = new AppSessionManager($this->createSessionManager());
        $manager->initialize($this->createRequest());

        $headers = $manager->saveIfOpenedAndGetHeaders();

        $this->assertSame([], $headers);
    }
}
