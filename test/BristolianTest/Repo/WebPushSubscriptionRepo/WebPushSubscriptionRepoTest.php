<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\WebPushSubscriptionRepo;

use Bristolian\Model\Types\UserWebPushSubscription;
use Bristolian\Parameters\WebPushSubscriptionParams;
use Bristolian\Repo\WebPushSubscriptionRepo\WebPushSubscriptionRepo;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;
use VarMap\ArrayVarMap;

/**
 * Abstract test class for WebPushSubscriptionRepo implementations.
 */
abstract class WebPushSubscriptionRepoTest extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * Get a test instance of the WebPushSubscriptionRepo implementation.
     *
     * @return WebPushSubscriptionRepo
     */
    abstract public function getTestInstance(): WebPushSubscriptionRepo;

    /**
     * Get a test user ID. Override in PDO tests to create actual user.
     */
    protected function getTestUserId(): string
    {
        return 'user-123';
    }

    /**
     * Get a second test user ID. Override in PDO tests to create actual user.
     */
    protected function getTestUserId2(): string
    {
        return 'user-456';
    }

    public function test_getUserSubscriptions_returns_empty_array_initially(): void
    {
        $repo = $this->getTestInstance();

        $subscriptions = $repo->getUserSubscriptions($this->getTestUserId());

        $this->assertIsArray($subscriptions);
        $this->assertEmpty($subscriptions);
    }

    public function test_save_stores_subscription(): void
    {
        $repo = $this->getTestInstance();

        $webPushSubscriptionParam = WebPushSubscriptionParams::createFromVarMap(new ArrayVarMap([
            'endpoint' => 'https://example.com/push/endpoint',
            'expirationTime' => null,
            'raw' => '{"raw": "subscription data"}',
        ]));

        // Should not throw exception
        $repo->save($this->getTestUserId(), $webPushSubscriptionParam, '{"raw": "subscription data"}');

        $this->assertTrue(true);
    }

    public function test_getUserSubscriptions_returns_saved_subscriptions(): void
    {
        $repo = $this->getTestInstance();

        $webPushSubscriptionParam = WebPushSubscriptionParams::createFromVarMap(new ArrayVarMap([
            'endpoint' => 'https://example.com/push/endpoint',
            'expirationTime' => null,
            'raw' => '{"raw": "subscription data"}',
        ]));

        $repo->save($this->getTestUserId(), $webPushSubscriptionParam, '{"raw": "subscription data"}');

        $subscriptions = $repo->getUserSubscriptions($this->getTestUserId());

        $this->assertCount(1, $subscriptions);
        $this->assertContainsOnlyInstancesOf(UserWebPushSubscription::class, $subscriptions);
        $this->assertSame('https://example.com/push/endpoint', $subscriptions[0]->getEndpoint());
    }

    public function test_getUserSubscriptions_returns_only_subscriptions_for_specified_user(): void
    {
        $repo = $this->getTestInstance();

        $param1 = WebPushSubscriptionParams::createFromVarMap(new ArrayVarMap([
            'endpoint' => 'https://example.com/push/endpoint1',
            'expirationTime' => null,
            'raw' => '{"raw": "data1"}',
        ]));
        $param2 = WebPushSubscriptionParams::createFromVarMap(new ArrayVarMap([
            'endpoint' => 'https://example.com/push/endpoint2',
            'expirationTime' => null,
            'raw' => '{"raw": "data2"}',
        ]));

        $repo->save('user-123', $param1, '{"raw": "data1"}');
        $repo->save('user-456', $param2, '{"raw": "data2"}');

        $subscriptions = $repo->getUserSubscriptions($this->getTestUserId());

        $this->assertCount(1, $subscriptions);
        $this->assertSame('https://example.com/push/endpoint1', $subscriptions[0]->getEndpoint());
    }

    public function test_getUserSubscriptions_returns_multiple_subscriptions_for_user(): void
    {
        $repo = $this->getTestInstance();

        $param1 = WebPushSubscriptionParams::createFromVarMap(new ArrayVarMap([
            'endpoint' => 'https://example.com/push/endpoint1',
            'expirationTime' => null,
            'raw' => '{"raw": "data1"}',
        ]));
        $param2 = WebPushSubscriptionParams::createFromVarMap(new ArrayVarMap([
            'endpoint' => 'https://example.com/push/endpoint2',
            'expirationTime' => null,
            'raw' => '{"raw": "data2"}',
        ]));

        $repo->save('user-123', $param1, '{"raw": "data1"}');
        $repo->save('user-123', $param2, '{"raw": "data2"}');

        $subscriptions = $repo->getUserSubscriptions($this->getTestUserId());

        $this->assertCount(2, $subscriptions);
    }

    public function test_save_with_expiration_time(): void
    {
        $repo = $this->getTestInstance();

        $webPushSubscriptionParam = WebPushSubscriptionParams::createFromVarMap(new ArrayVarMap([
            'endpoint' => 'https://example.com/push/endpoint',
            'expirationTime' => '1234567890',
            'raw' => '{"raw": "subscription data"}',
        ]));

        // Should not throw exception
        $repo->save($this->getTestUserId(), $webPushSubscriptionParam, '{"raw": "subscription data"}');

        $subscriptions = $repo->getUserSubscriptions($this->getTestUserId());
        $this->assertCount(1, $subscriptions);
        $this->assertSame('1234567890', $subscriptions[0]->getExpirationTime());
    }
}
