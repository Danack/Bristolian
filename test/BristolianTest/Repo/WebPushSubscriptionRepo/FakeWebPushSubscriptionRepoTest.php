<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\WebPushSubscriptionRepo;

use Bristolian\Repo\WebPushSubscriptionRepo\FakeWebPushSubscriptionRepo;
use Bristolian\Repo\WebPushSubscriptionRepo\UserConstraintFailedException;
use Bristolian\Repo\WebPushSubscriptionRepo\WebPushSubscriptionRepo;
use Bristolian\Parameters\WebPushSubscriptionParams;
use VarMap\ArrayVarMap;

/**
 * @group standard_repo
 * @coversNothing
 */
class FakeWebPushSubscriptionRepoTest extends WebPushSubscriptionRepoFixture
{
    public function getTestInstance(): WebPushSubscriptionRepo
    {
        return new FakeWebPushSubscriptionRepo();
    }

    /**
     * Fake-specific test: verify duplicate endpoint throws exception
     */
    public function test_save_throws_exception_for_duplicate_endpoint(): void
    {
        $fakeRepo = new FakeWebPushSubscriptionRepo();

        $webPushSubscriptionParam = WebPushSubscriptionParams::createFromVarMap(new ArrayVarMap([
            'endpoint' => 'https://example.com/push/endpoint',
            'expirationTime' => null,
            'raw' => '{"raw": "subscription data"}',
        ]));

        $fakeRepo->save('user-123', $webPushSubscriptionParam, '{"raw": "subscription data"}');

        // Attempting to save the same endpoint again should throw
        $this->expectException(UserConstraintFailedException::class);
        $fakeRepo->save('user-123', $webPushSubscriptionParam, '{"raw": "subscription data"}');
    }

    /**
     * Fake-specific test: verify different users can have same endpoint
     */
    public function test_save_allows_same_endpoint_for_different_users(): void
    {
        $fakeRepo = new FakeWebPushSubscriptionRepo();

        $webPushSubscriptionParam = WebPushSubscriptionParams::createFromVarMap(new ArrayVarMap([
            'endpoint' => 'https://example.com/push/endpoint',
            'expirationTime' => null,
            'raw' => '{"raw": "subscription data"}',
        ]));

        // Should not throw - different users can have same endpoint
        $fakeRepo->save('user-123', $webPushSubscriptionParam, '{"raw": "data1"}');
        $fakeRepo->save('user-456', $webPushSubscriptionParam, '{"raw": "data2"}');
    }

    /**
     * @covers \Bristolian\Repo\WebPushSubscriptionRepo\FakeWebPushSubscriptionRepo::getUserSubscriptions
     */
    public function test_getUserSubscriptions_returns_empty_for_unknown_user(): void
    {
        $fakeRepo = new FakeWebPushSubscriptionRepo();
        $this->assertSame([], $fakeRepo->getUserSubscriptions('unknown-user'));
    }

    /**
     * @covers \Bristolian\Repo\WebPushSubscriptionRepo\FakeWebPushSubscriptionRepo::getUserSubscriptions
     * @covers \Bristolian\Repo\WebPushSubscriptionRepo\FakeWebPushSubscriptionRepo::save
     */
    public function test_getUserSubscriptions_returns_saved_subscriptions(): void
    {
        $fakeRepo = new FakeWebPushSubscriptionRepo();
        $params = WebPushSubscriptionParams::createFromVarMap(new ArrayVarMap([
            'endpoint' => 'https://example.com/push/1',
            'expirationTime' => null,
            'raw' => '{"raw": "data"}',
        ]));
        $fakeRepo->save('user-1', $params, '{"raw": "data"}');
        $subs = $fakeRepo->getUserSubscriptions('user-1');
        $this->assertCount(1, $subs);
        $this->assertSame('https://example.com/push/1', $subs[0]->getEndpoint());
    }
}
