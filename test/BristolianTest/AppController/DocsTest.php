<?php

declare(strict_types = 1);

namespace BristolianTest\AppController;

use Bristolian\AppController\Docs;
use Bristolian\Session\UserSession;
use Bristolian\Session\FakeUserSession;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class DocsTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\AppController\Docs::index
     */
    public function test_index(): void
    {
        $result = $this->injector->execute([Docs::class, 'index']);
        $this->assertIsString($result);
        $this->assertStringContainsString('Docs', $result);
    }

    /**
     * @covers \Bristolian\AppController\Docs::files
     */
    public function test_files(): void
    {
        $result = $this->injector->execute([Docs::class, 'files']);
        $this->assertIsString($result);
        $this->assertStringContainsString('Files', $result);
    }

    /**
     * @covers \Bristolian\AppController\Docs::memes
     */
    public function test_memes_logged_in(): void
    {
        $this->setupFakeUserSession();

        $result = $this->injector->execute([Docs::class, 'memes']);
        $this->assertIsString($result);
        $this->assertStringContainsString('meme_upload_panel', $result);
    }

    /**
     * @covers \Bristolian\AppController\Docs::memes
     */
    public function test_memes_not_logged_in(): void
    {
        $session = new FakeUserSession(false, '', '');
        $this->injector->alias(UserSession::class, FakeUserSession::class);
        $this->injector->share($session);

        $result = $this->injector->execute([Docs::class, 'memes']);
        $this->assertIsString($result);
        $this->assertStringContainsString('not logged in', $result);
    }
}
