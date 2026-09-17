<?php

declare(strict_types=1);

namespace BristolianTest\Session;

use Bristolian\Exception\BristolianException;
use Bristolian\Session\FakeAppSessionManager;
use BristolianTest\BaseTestCase;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Session\FakeAppSessionManager::class, '__construct')]
#[CoversMethod(\Bristolian\Session\FakeAppSessionManager::class, 'createLoggedIn')]
#[CoversMethod(\Bristolian\Session\FakeAppSessionManager::class, 'createRawSession')]
#[CoversMethod(\Bristolian\Session\FakeAppSessionManager::class, 'deleteSession')]
#[CoversMethod(\Bristolian\Session\FakeAppSessionManager::class, 'getCurrentAppSession')]
#[CoversMethod(\Bristolian\Session\FakeAppSessionManager::class, 'initialize')]
#[CoversMethod(\Bristolian\Session\FakeAppSessionManager::class, 'renewSession')]
#[CoversMethod(\Bristolian\Session\FakeAppSessionManager::class, 'saveIfOpenedAndGetHeaders')]

class FakeAppSessionManagerTest extends BaseTestCase
{
    public function test_getCurrentAppSession_returns_null_by_default(): void
    {
        $manager = new FakeAppSessionManager();
        $this->assertNull($manager->getCurrentAppSession());
    }

    public function test_createLoggedIn_returns_logged_in_session(): void
    {
        $manager = FakeAppSessionManager::createLoggedIn();
        $session = $manager->getCurrentAppSession();

        $this->assertNotNull($session);
        $this->assertTrue($session->isLoggedIn());
        $this->assertSame('abcde123345', $session->getUserId());
        $this->assertSame('john', $session->getUsername());
    }

    public function test_initialize_does_not_throw(): void
    {
        $manager = new FakeAppSessionManager();
        $request = new ServerRequest();

        $manager->initialize($request);

        $this->assertNull($manager->getCurrentAppSession());
    }

    public function test_deleteSession_throws(): void
    {
        $manager = new FakeAppSessionManager();

        $this->expectException(BristolianException::class);
        $this->expectExceptionMessage('Not implemented');

        $manager->deleteSession();
    }

    public function test_createRawSession_throws(): void
    {
        $manager = new FakeAppSessionManager();

        $this->expectException(BristolianException::class);
        $this->expectExceptionMessage('Not implemented');

        $manager->createRawSession();
    }

    public function test_renewSession_returns_default_headers(): void
    {
        $manager = new FakeAppSessionManager();
        $headers = $manager->renewSession();

        $this->assertCount(2, $headers);
        $this->assertSame('set-cookie', $headers[0][0]);
        $this->assertStringContainsString('john_is_my_name', $headers[0][1]);
    }

    public function test_renewSession_returns_custom_headers_when_provided(): void
    {
        $customHeaders = [['X-Custom', 'value']];
        $manager = new FakeAppSessionManager($customHeaders);

        $headers = $manager->renewSession();

        $this->assertSame($customHeaders, $headers);
    }

    public function test_saveIfOpenedAndGetHeaders_returns_default_headers(): void
    {
        $manager = new FakeAppSessionManager();
        $headers = $manager->saveIfOpenedAndGetHeaders();

        $this->assertCount(2, $headers);
        $this->assertSame('set-cookie', $headers[0][0]);
        $this->assertStringContainsString('john_is_my_name', $headers[0][1]);
    }

    public function test_saveIfOpenedAndGetHeaders_returns_custom_headers_when_provided(): void
    {
        $customHeaders = [['X-Test', 'header-value']];
        $manager = new FakeAppSessionManager($customHeaders);

        $headers = $manager->saveIfOpenedAndGetHeaders();

        $this->assertSame($customHeaders, $headers);
    }
}
