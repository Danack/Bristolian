<?php

declare(strict_types=1);

namespace BristolianTest\Repo\WebPushSubscriptionRepo;

use Bristolian\DataType\BasicString;
use Bristolian\DataType\CreateUserParams;
use Bristolian\DataType\WebPushEndPoint;
use Bristolian\DataType\WebPushSubscriptionParam;
use Bristolian\Repo\AdminRepo\PdoAdminRepo;
use BristolianTest\Repo\TestPlaceholders;
use VarMap\ArrayVarMap;
use BristolianTest\BaseTestCase;
use Bristolian\Repo\WebPushSubscriptionRepo\PdoWebPushSubscriptionRepo;
use Bristolian\Repo\WebPushSubscriptionRepo\UserConstraintFailedException;


/**
 * @coversNothing
 */
class PdoWebPushSubscriptionRepoTest extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * @covers \Bristolian\Repo\WebPushSubscriptionRepo\PdoWebPushSubscriptionRepo
     * @group db
     * @group wip
     */
    public function testWorks(): void
    {
        $endpoint = "https://www.example.com/?time=" . microtime();
        $expiration_time = 'some time string';
        $raw = 'Some raw WebPushSubscriptionParam';

        $webPushSubParams = WebPushSubscriptionParam::createFromArray([
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
    }

    public function testExceptionOnInvalidUser()
    {
        $webpush_repo = $this->injector->make(PdoWebPushSubscriptionRepo::class);

        $endpoint = "https://www.example.com/?time=" . microtime();
        $expiration_time = 'some time string';
        $raw = 'Some raw WebPushSubscriptionParam';

        $webPushSubParams = WebPushSubscriptionParam::createFromArray([
            'endpoint' => $endpoint,
            'expirationTime' => $expiration_time,
            'raw' => $raw
        ]);

        $this->expectException(UserConstraintFailedException::class);
        $webpush_repo->save("non-existent user", $webPushSubParams, $raw);
    }
}
