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

    /**
     * @covers \Bristolian\AppController\Tools::floating_point_page
     */
    public function test_floating_point_page(): void
    {
        $result = $this->injector->execute([Tools::class, 'floating_point_page']);
        $this->assertIsString($result);
        $this->assertStringContainsString('floating_point_panel', $result);
    }

    /**
     * @covers \Bristolian\AppController\Tools::floating_point_page_8
     */
    public function test_floating_point_page_8(): void
    {
        $result = $this->injector->execute([Tools::class, 'floating_point_page_8']);
        $this->assertIsString($result);
        $this->assertStringContainsString('floating_point_8_bit_panel', $result);
    }

    /**
     * @covers \Bristolian\AppController\Tools::timeline_page
     */
    public function test_timeline_page(): void
    {
        $result = $this->injector->execute([Tools::class, 'timeline_page']);
        $this->assertIsString($result);
        $this->assertStringContainsString('time_line_panel', $result);
    }

    /**
     * @covers \Bristolian\AppController\Tools::teleprompter_page
     */
    public function test_teleprompter_page(): void
    {
        $result = $this->injector->execute([Tools::class, 'teleprompter_page']);
        $this->assertIsString($result);
        $this->assertStringContainsString('teleprompter_panel', $result);
    }

    /**
     * @covers \Bristolian\AppController\Tools::email_link_generator_page
     */
    public function test_email_link_generator_page(): void
    {
        $result = $this->injector->execute([Tools::class, 'email_link_generator_page']);
        $this->assertIsString($result);
        $this->assertStringContainsString('email_link_generator_panel', $result);
    }

    /**
     * @covers \Bristolian\AppController\Tools::qr_code_generator_page
     */
    public function test_qr_code_generator_page(): void
    {
        $result = $this->injector->execute([Tools::class, 'qr_code_generator_page']);
        $this->assertIsString($result);
        $this->assertStringContainsString('qr_code_generator_panel', $result);
    }

    /**
     * @covers \Bristolian\AppController\Tools::notes_page
     */
    public function test_notes_page(): void
    {
        $result = $this->injector->execute([Tools::class, 'notes_page']);
        $this->assertIsString($result);
        $this->assertStringContainsString('notes_panel', $result);
    }

    /**
     * @covers \Bristolian\AppController\Tools::twitter_splitter_page
     */
    public function test_twitter_splitter_page(): void
    {
        $result = $this->injector->execute([Tools::class, 'twitter_splitter_page']);
        $this->assertIsString($result);
        $this->assertStringContainsString('twitter_splitter_panel', $result);
    }

    /**
     * @covers \Bristolian\AppController\Tools::committee_seats_page
     */
    public function test_committee_seats_page(): void
    {
        $result = $this->injector->execute([Tools::class, 'committee_seats_page']);
        $this->assertIsString($result);
        $this->assertStringContainsString('committee_seats_app', $result);
        $this->assertStringContainsString('committee_seats_panel', $result);
    }
}
