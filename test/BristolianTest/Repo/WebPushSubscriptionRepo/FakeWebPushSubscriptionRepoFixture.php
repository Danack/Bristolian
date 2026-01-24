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
 */
class FakeWebPushSubscriptionRepoFixture extends WebPushSubscriptionRepoFixture
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
}
