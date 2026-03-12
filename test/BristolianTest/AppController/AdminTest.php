<?php

declare(strict_types = 1);

namespace BristolianTest\AppController;

use Bristolian\AppController\Admin;
use Bristolian\Repo\ProcessorRepo\FakeProcessorRepo;
use Bristolian\Service\UnknownCacheQueries\InMemoryUnknownCacheQueriesProvider;
use Bristolian\Service\UnknownCacheQueries\UnknownCacheQueriesProvider;
use Bristolian\Repo\ProcessorRepo\ProcessType;
use Bristolian\Repo\ProcessorRepo\ProcessorRepo;
use Bristolian\Repo\UserSearch\InMemoryUserSearch;
use Bristolian\Repo\UserSearch\UserSearch;
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
        $this->injector->alias(UnknownCacheQueriesProvider::class, InMemoryUnknownCacheQueriesProvider::class);
        $this->injector->share(InMemoryUnknownCacheQueriesProvider::class);
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
     * @covers \Bristolian\AppController\Admin::showProcessorsPage
     * @covers \Bristolian\AppController\Admin::renderProcessorLogWidget
     */
    public function test_showProcessorsPage_with_enabled_processor_shows_enabled_state(): void
    {
        $processorRepo = $this->injector->make(FakeProcessorRepo::class);
        $processorRepo->setProcessorEnabled(ProcessType::daily_system_info, true);

        $result = $this->injector->execute([Admin::class, 'showProcessorsPage']);

        $this->assertInstanceOf(HtmlNoCacheResponse::class, $result);
        $this->assertStringContainsString('Enabled', $result->getBody());
        $this->assertStringContainsString('disable', $result->getBody());
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
    public function test_updateProcessors_invalid_processor(): void
    {
        // ProcessType::daily_bcc_tro is a valid enum but not listed in Admin::$processors
        $varMap = new ArrayVarMap([
            'processor' => ProcessType::daily_bcc_tro->value,
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
     * @covers \Bristolian\AppController\Admin::updateProcessors
     */
    public function test_updateProcessors_disable(): void
    {
        $varMap = new ArrayVarMap([
            'processor' => 'daily_system_info',
            'action' => 'disable',
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
        $this->assertStringContainsString('No unknown queries logged.', $result);
    }

    /**
     * @covers \Bristolian\AppController\Admin::showUnknownCacheQueries
     */
    public function test_showUnknownCacheQueries_with_queries_shows_table(): void
    {
        $provider = new InMemoryUnknownCacheQueriesProvider();
        $provider->addKey('key-one');
        $provider->setQuery('key-one', 'SELECT * FROM users');
        $provider->addKey('key-two');
        $provider->setQuery('key-two', 'SELECT * FROM rooms');
        $this->injector->share($provider);

        $result = $this->injector->execute([Admin::class, 'showUnknownCacheQueries']);
        $this->assertIsString($result);
        $this->assertStringContainsString('2 unknown queries found.', $result);
        $this->assertStringContainsString('<table>', $result);
        $this->assertStringContainsString('SELECT * FROM users', $result);
        $this->assertStringContainsString('SELECT * FROM rooms', $result);
    }

    /**
     * @covers \Bristolian\AppController\Admin::showUnknownCacheQueries
     */
    public function test_showUnknownCacheQueries_skips_key_with_no_query(): void
    {
        $provider = new InMemoryUnknownCacheQueriesProvider();
        $provider->addKey('key-with-query');
        $provider->setQuery('key-with-query', 'SELECT 1');
        $provider->addKey('key-missing-query');
        // do not setQuery for key-missing-query so getQuery returns false
        $this->injector->share($provider);

        $result = $this->injector->execute([Admin::class, 'showUnknownCacheQueries']);
        $this->assertIsString($result);
        $this->assertStringContainsString('2 unknown queries found.', $result);
        $this->assertStringContainsString('SELECT 1', $result);
        $this->assertStringNotContainsString('key-missing-query', $result);
    }
}
