<?php

declare(strict_types=1);

namespace BristolianTest\AppController;

use Asm\Encrypter\NullEncrypterFactory;
use Asm\SessionConfig;
use Asm\SessionManager;
use Bristolian\AppController\Login;
use Bristolian\Parameters\CreateUserParams;
use Bristolian\Repo\AdminRepo\AdminRepo;
use Bristolian\Repo\AdminRepo\FakeAdminRepo;
use Bristolian\Session\AppSession;
use Bristolian\Session\AppSessionManager;
use Bristolian\Session\FakeAsmDriver;
use BristolianTest\BaseTestCase;
use BristolianTest\Session\FakeAsmSession;
use Laminas\Diactoros\ServerRequest;
use SlimDispatcher\Response\RedirectResponse;

/**
 * @coversNothing
 */
class LoginTest extends BaseTestCase
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
     * @covers \Bristolian\AppController\Login::logout
     */
    public function test_logout(): void
    {
        $sessionManager = $this->createSessionManager();
        $appSessionManager = new AppSessionManager($sessionManager);
        $appSessionManager->initialize($this->createRequest());
        $this->injector->share($appSessionManager);

        $result = $this->injector->execute([Login::class, 'logout']);

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Login::showLoginPage
     */
    public function test_showLoginPage_when_not_logged_in_returns_form(): void
    {
        $sessionManager = $this->createSessionManager();
        $appSessionManager = new AppSessionManager($sessionManager);
        $appSessionManager->initialize($this->createRequest());
        $this->injector->share($appSessionManager);

        $result = $this->injector->execute([Login::class, 'showLoginPage']);

        $this->assertIsString($result);
        $this->assertStringContainsString('<form method="post">', $result);
        $this->assertStringContainsString('name="username"', $result);
        $this->assertStringContainsString('name="password"', $result);
    }

    /**
     * @covers \Bristolian\AppController\Login::showLoginPage
     */
    public function test_showLoginPage_when_logged_in_redirects(): void
    {
        $existingSession = new FakeAsmSession('sess-loggedin');
        $existingSession->set(AppSession::LOGGED_IN, true);
        $existingSession->set(AppSession::USER_ID, 'user-1');
        $existingSession->set(AppSession::USERNAME, 'admin@example.com');

        $driver = new FakeAsmDriver();
        $driver->addSession($existingSession);
        $sessionManager = $this->createSessionManager($driver);
        $appSessionManager = new AppSessionManager($sessionManager);
        $appSessionManager->initialize($this->createRequest(['test_session' => 'sess-loggedin']));
        $this->injector->share($appSessionManager);

        $result = $this->injector->execute([Login::class, 'showLoginPage']);

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Login::processLoginPage
     */
    public function test_processLoginPage_success_redirects_to_tools(): void
    {
        $adminRepo = new FakeAdminRepo([]);
        $adminRepo->addUser(CreateUserParams::createFromArray([
            'email_address' => 'admin@test.com',
            'password' => 'secret',
        ]));
        $this->injector->alias(AdminRepo::class, FakeAdminRepo::class);
        $this->injector->share($adminRepo);

        $sessionManager = $this->createSessionManager();
        $appSessionManager = new AppSessionManager($sessionManager);
        $appSessionManager->initialize($this->createRequest());
        $this->injector->share($appSessionManager);

        $previousPost = $_POST;
        $_POST = ['username' => 'admin@test.com', 'password' => 'secret'];
        try {
            $result = $this->injector->execute([Login::class, 'processLoginPage']);
            $this->assertInstanceOf(RedirectResponse::class, $result);
        } finally {
            $_POST = $previousPost;
        }
    }

    /**
     * @covers \Bristolian\AppController\Login::processLoginPage
     */
    public function test_processLoginPage_missing_username_redirects_to_login(): void
    {
        $adminRepo = new FakeAdminRepo([]);
        $this->injector->alias(AdminRepo::class, FakeAdminRepo::class);
        $this->injector->share($adminRepo);

        $sessionManager = $this->createSessionManager();
        $appSessionManager = new AppSessionManager($sessionManager);
        $appSessionManager->initialize($this->createRequest());
        $this->injector->share($appSessionManager);

        $previousPost = $_POST;
        $_POST = [];
        try {
            $result = $this->injector->execute([Login::class, 'processLoginPage']);
            $this->assertInstanceOf(RedirectResponse::class, $result);
        } finally {
            $_POST = $previousPost;
        }
    }

    /**
     * @covers \Bristolian\AppController\Login::processLoginPage
     */
    public function test_processLoginPage_missing_password_redirects_to_login(): void
    {
        $adminRepo = new FakeAdminRepo([]);
        $this->injector->alias(AdminRepo::class, FakeAdminRepo::class);
        $this->injector->share($adminRepo);

        $sessionManager = $this->createSessionManager();
        $appSessionManager = new AppSessionManager($sessionManager);
        $appSessionManager->initialize($this->createRequest());
        $this->injector->share($appSessionManager);

        $previousPost = $_POST;
        $_POST = ['username' => 'someone@example.com'];
        try {
            $result = $this->injector->execute([Login::class, 'processLoginPage']);
            $this->assertInstanceOf(RedirectResponse::class, $result);
        } finally {
            $_POST = $previousPost;
        }
    }

    /**
     * @covers \Bristolian\AppController\Login::processLoginPage
     */
    public function test_processLoginPage_wrong_credentials_redirects_to_login(): void
    {
        $adminRepo = new FakeAdminRepo([]);
        $adminRepo->addUser(CreateUserParams::createFromArray([
            'email_address' => 'admin@test.com',
            'password' => 'secret',
        ]));
        $this->injector->alias(AdminRepo::class, FakeAdminRepo::class);
        $this->injector->share($adminRepo);

        $sessionManager = $this->createSessionManager();
        $appSessionManager = new AppSessionManager($sessionManager);
        $appSessionManager->initialize($this->createRequest());
        $this->injector->share($appSessionManager);

        $previousPost = $_POST;
        $_POST = ['username' => 'admin@test.com', 'password' => 'wrong'];
        try {
            $result = $this->injector->execute([Login::class, 'processLoginPage']);
            $this->assertInstanceOf(RedirectResponse::class, $result);
        } finally {
            $_POST = $previousPost;
        }
    }
}
