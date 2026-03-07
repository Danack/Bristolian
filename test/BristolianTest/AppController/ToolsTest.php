<?php

declare(strict_types = 1);

namespace BristolianTest\AppController;

use Bristolian\AppController\Tools;
use Bristolian\Session\OptionalUserSession;
use Bristolian\Session\StandardOptionalUserSession;
use BristolianTest\BaseTestCase;

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
}
