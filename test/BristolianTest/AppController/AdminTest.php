<?php

declare(strict_types = 1);

namespace BristolianTest\AppController;

use Bristolian\AppController\Admin;
use Bristolian\Repo\ProcessorRepo\ProcessorRepo;
use Bristolian\Repo\ProcessorRepo\FakeProcessorRepo;
use Bristolian\Repo\UserSearch\UserSearch;
use Bristolian\Repo\UserSearch\InMemoryUserSearch;
use BristolianTest\BaseTestCase;
use SlimDispatcher\Response\HtmlNoCacheResponse;
use SlimDispatcher\Response\JsonResponse;
use SlimDispatcher\Response\RedirectResponse;
use VarMap\ArrayVarMap;
use VarMap\VarMap;

/**
 * @coversNothing
 */
class AdminTest extends BaseTestCase
{
    public function setup(): void
    {
        parent::setup();
        $this->injector->alias(ProcessorRepo::class, FakeProcessorRepo::class);
        $this->injector->share(FakeProcessorRepo::class);
        $this->injector->alias(UserSearch::class, InMemoryUserSearch::class);
        $this->injector->share(InMemoryUserSearch::class);
        $this->injector->alias(
            \Bristolian\Config\AssetLinkEmitterConfig::class,
            \Bristolian\Config\Config::class
        );
    }

    /**
     * @covers \Bristolian\AppController\Admin::showNotificationTestPage
     */
    public function test_showNotificationTestPage(): void
    {
        $result = $this->injector->execute([Admin::class, 'showNotificationTestPage']);
        $this->assertIsString($result);
        $this->assertStringContainsString('notification_test_panel', $result);
    }

    /**
     * @covers \Bristolian\AppController\Admin::showAdminPage
     */
    public function test_showAdminPage(): void
    {
        $result = $this->injector->execute([Admin::class, 'showAdminPage']);
        $this->assertIsString($result);
        $this->assertStringContainsString('Admin page', $result);
    }

    /**
     * @covers \Bristolian\AppController\Admin::showEmailPage
     */
    public function test_showEmailPage(): void
    {
        $result = $this->injector->execute([Admin::class, 'showEmailPage']);
        $this->assertIsString($result);
        $this->assertStringContainsString('admin_email_panel', $result);
    }

    /**
     * @covers \Bristolian\AppController\Admin::showProcessorsPage
     * @covers \Bristolian\AppController\Admin::renderProcessorLogWidget
     */
    public function test_showProcessorsPage(): void
    {
        $result = $this->injector->execute([Admin::class, 'showProcessorsPage']);
        $this->assertInstanceOf(HtmlNoCacheResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Admin::updateProcessors
     */
    public function test_updateProcessors_enable(): void
    {
        $varMap = new ArrayVarMap([
            'processor' => 'daily_system_info',
            'action' => 'enable',
        ]);
        $this->injector->alias(VarMap::class, ArrayVarMap::class);
        $this->injector->share($varMap);
        $this->setupFakeUserSession();

        $result = $this->injector->execute([Admin::class, 'updateProcessors']);
        $this->assertInstanceOf(RedirectResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Admin::updateProcessors
     */
    public function test_updateProcessors_no_processor(): void
    {
        $varMap = new ArrayVarMap([]);
        $this->injector->alias(VarMap::class, ArrayVarMap::class);
        $this->injector->share($varMap);
        $this->setupFakeUserSession();

        $result = $this->injector->execute([Admin::class, 'updateProcessors']);
        $this->assertInstanceOf(RedirectResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Admin::updateProcessors
     */
    public function test_updateProcessors_no_action(): void
    {
        $varMap = new ArrayVarMap([
            'processor' => 'daily_system_info',
        ]);
        $this->injector->alias(VarMap::class, ArrayVarMap::class);
        $this->injector->share($varMap);
        $this->setupFakeUserSession();

        $result = $this->injector->execute([Admin::class, 'updateProcessors']);
        $this->assertInstanceOf(RedirectResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Admin::updateProcessors
     */
    public function test_updateProcessors_invalid_action(): void
    {
        $varMap = new ArrayVarMap([
            'processor' => 'daily_system_info',
            'action' => 'invalid',
        ]);
        $this->injector->alias(VarMap::class, ArrayVarMap::class);
        $this->injector->share($varMap);
        $this->setupFakeUserSession();

        $result = $this->injector->execute([Admin::class, 'updateProcessors']);
        $this->assertInstanceOf(RedirectResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Admin::search_users
     */
    public function test_search_users(): void
    {
        $userSearch = $this->injector->make(InMemoryUserSearch::class);
        $userSearch->addEmailAddress('alice@example.com');
        $userSearch->addEmailAddress('bob@example.com');

        $varMap = new ArrayVarMap(['user_search' => 'ali']);
        $this->injector->alias(VarMap::class, ArrayVarMap::class);
        $this->injector->share($varMap);

        $result = $this->injector->execute([Admin::class, 'search_users']);
        $this->assertInstanceOf(JsonResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Admin::search_users
     */
    public function test_search_users_no_query(): void
    {
        $varMap = new ArrayVarMap([]);
        $this->injector->alias(VarMap::class, ArrayVarMap::class);
        $this->injector->share($varMap);

        $result = $this->injector->execute([Admin::class, 'search_users']);
        $this->assertInstanceOf(JsonResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Admin::showUnknownCacheQueries
     */
    public function test_showUnknownCacheQueries(): void
    {
        $result = $this->injector->execute([Admin::class, 'showUnknownCacheQueries']);
        $this->assertIsString($result);
        $this->assertStringContainsString('Unknown Cache Queries', $result);
    }
}
