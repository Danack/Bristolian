<?php

declare(strict_types=1);

namespace BristolianTest\Repo\WebPushSubscriptionRepo;

use Bristolian\Parameters\PropertyType\BasicString;
use Bristolian\Parameters\CreateUserParams;
use Bristolian\Parameters\PropertyType\WebPushEndPoint;
use Bristolian\Parameters\WebPushSubscriptionParams;
use Bristolian\Repo\AdminRepo\PdoAdminRepo;
use BristolianTest\Repo\TestPlaceholders;
use VarMap\ArrayVarMap;
use Bristolian\Repo\WebPushSubscriptionRepo\WebPushSubscriptionRepo;
use Bristolian\Repo\WebPushSubscriptionRepo\PdoWebPushSubscriptionRepo;
use Bristolian\Repo\WebPushSubscriptionRepo\UserConstraintFailedException;

/**
 * @group db
 */
class PdoWebPushSubscriptionRepoTest extends WebPushSubscriptionRepoTest
{
    use TestPlaceholders;

    private ?string $testUserId = null;
    private ?string $testUserId2 = null;

    public function getTestInstance(): WebPushSubscriptionRepo
    {
        return $this->injector->make(PdoWebPushSubscriptionRepo::class);
    }

    protected function getTestUserId(): string
    {
        if ($this->testUserId === null) {
            $adminUser = $this->createTestAdminUser();
            $this->testUserId = $adminUser->getUserId();
        }
        return $this->testUserId;
    }

    protected function getTestUserId2(): string
    {
        if ($this->testUserId2 === null) {
            $adminUser = $this->createTestAdminUser();
            $this->testUserId2 = $adminUser->getUserId();
        }
        return $this->testUserId2;
    }

    /**
     * @covers \Bristolian\Repo\WebPushSubscriptionRepo\PdoWebPushSubscriptionRepo
     * @group db
     */
    public function testWorks(): void
    {
        $endpoint = "https://www.example.com/?time=" . microtime();
        $expiration_time = 'some time string';
        $raw = 'Some raw WebPushSubscriptionParam';

        $webPushSubParams = WebPushSubscriptionParams::createFromArray([
            'endpoint' => $endpoint,
            'expirationTime' => $expiration_time,
            'raw' => $raw
        ]);

        $testUser = $this->createTestAdminUser();
        $webpush_repo = $this->injector->make(PdoWebPushSubscriptionRepo::class);
        $webpush_repo->save($testUser->getUserId(), $webPushSubParams, $raw);

        $subscriptions = $webpush_repo->getUserSubscriptions($testUser->getUserId());
        $this->assertCount(1, $subscriptions);
        $subscription = $subscriptions[0];

        $this->assertSame($endpoint, $subscription->getEndpoint());
        $this->assertSame($expiration_time, $subscription->getExpirationTime());
        $this->assertSame($raw, $subscription->getRaw());

        $this->expectException(UserConstraintFailedException::class);
        $webpush_repo->save('invalid_user_id', $webPushSubParams, $raw);
    }

    public function testExceptionOnInvalidUser()
    {
        $webpush_repo = $this->injector->make(PdoWebPushSubscriptionRepo::class);

        $endpoint = "https://www.example.com/?time=" . microtime();
        $expiration_time = 'some time string';
        $raw = 'Some raw WebPushSubscriptionParam';

        $webPushSubParams = WebPushSubscriptionParams::createFromArray([
            'endpoint' => $endpoint,
            'expirationTime' => $expiration_time,
            'raw' => $raw
        ]);

        $this->expectException(UserConstraintFailedException::class);
        $webpush_repo->save("non-existent user", $webPushSubParams, $raw);
    }
}
