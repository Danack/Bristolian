<?php

declare(strict_types = 1);

namespace BristolianTest\AppController;

use Bristolian\AppController\Tools;
use Bristolian\Session\AppSession;
use Bristolian\Session\OptionalUserSession;
use Bristolian\Session\StandardOptionalUserSession;
use BristolianTest\BaseTestCase;
use BristolianTest\Session\FakeAsmSession;

/**
 * @coversNothing
 */
class ToolsTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\AppController\Tools::index
     */
    public function test_index_not_logged_in(): void
    {
        $optionalSession = new StandardOptionalUserSession(null);
        $this->injector->alias(OptionalUserSession::class, StandardOptionalUserSession::class);
        $this->injector->share($optionalSession);

        $result = $this->injector->execute([Tools::class, 'index']);
        $this->assertIsString($result);
        $this->assertStringContainsString('not logged in', $result);
        $this->assertStringContainsString('Tools page', $result);
    }

    /**
     * @covers \Bristolian\AppController\Tools::index
     */
    public function test_index_logged_in(): void
    {
        $rawSession = new FakeAsmSession();
        $rawSession->set(AppSession::USER_ID, 'test-user-001');
        $appSession = new AppSession($rawSession);
        $appSession->setUsername('alice@example.com');

        $optionalSession = new StandardOptionalUserSession($appSession);
        $this->injector->alias(OptionalUserSession::class, StandardOptionalUserSession::class);
        $this->injector->share($optionalSession);

        $result = $this->injector->execute([Tools::class, 'index']);
        $this->assertIsString($result);
        $this->assertStringContainsString('alice@example.com', $result);
        $this->assertStringContainsString('Tools page', $result);
        $this->assertStringNotContainsString('not logged in', $result);
    }
}
