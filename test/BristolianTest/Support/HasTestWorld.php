<?php

declare(strict_types = 1);

namespace BristolianTest\Support;

use Bristolian\Session\FakeUserSession;

/**
 * Trait that provides access to TestWorld and StandardTestData for tests.
 *
 * Tests that use this trait can access shared test data through standardTestData(),
 * and configure session state via useLoggedInUser() / useAnonymousUser().
 * See docs/refactoring/default_test_scenarios_and_worlds.md.
 */
trait HasTestWorld
{
    private ?TestWorld $testWorld = null;
    private ?StandardTestData $standardTestData = null;

    /**
     * Get the test world, creating it if needed.
     */
    protected function world(): TestWorld
    {
        if ($this->testWorld === null) {
            $this->testWorld = new TestWorld($this->injector);
        }
        return $this->testWorld;
    }

    /**
     * Get the standard test data helper, creating it if needed.
     */
    protected function standardTestData(): StandardTestData
    {
        if ($this->standardTestData === null) {
            $this->standardTestData = new StandardTestData($this->world());
        }
        return $this->standardTestData;
    }

    /**
     * Ensure the standard test setup exists.
     * Idempotent; safe to call multiple times.
     */
    protected function ensureStandardSetup(): void
    {
        $this->standardTestData()->ensureStandardSetup();
    }

    /**
     * Configure the injector so the current user session is logged in as the
     * standard test user (testing@example.com). Call in setUp() or at the
     * start of a test when the code under test expects an authenticated user.
     */
    protected function useLoggedInUser(): void
    {
        $this->ensureStandardSetup();
        $userId = $this->standardTestData()->getTestingUserId();
        $session = new FakeUserSession(true, $userId, 'testing@example.com');
        $this->injector->alias(\Bristolian\Session\UserSession::class, FakeUserSession::class);
        $this->injector->share($session);
    }

    /**
     * Configure the injector so the current user session is anonymous (not
     * logged in). Call in setUp() or at the start of a test when the code
     * under test expects an unauthenticated user.
     */
    protected function useAnonymousUser(): void
    {
        $session = new FakeUserSession(false, '', '');
        $this->injector->alias(\Bristolian\Session\UserSession::class, FakeUserSession::class);
        $this->injector->share($session);
    }
}
