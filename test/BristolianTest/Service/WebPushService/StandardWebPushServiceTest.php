<?php

declare(strict_types=1);

namespace BristolianTest\Service\WebPushService;

use Bristolian\Model\Types\UserWebPushSubscription;
use Bristolian\Model\Types\WebPushNotification;
use Bristolian\Service\WebPushService\StandardWebPushService;
use BristolianTest\BaseTestCase;
use Minishlink\WebPush\SubscriptionInterface;

/**
 * @coversNothing
 */
class StandardWebPushServiceTest extends BaseTestCase
{

    /**
     * @covers \Bristolian\Service\WebPushService\StandardWebPushService::__construct
     * @covers \Bristolian\Service\WebPushService\StandardWebPushService::sendWebPushToSubscriptions
     */
    public function test_sendWebPushToSubscriptions_sends_notifications_and_returns_empty_array_on_success(): void
    {
        $fakeWebPush = new FakeWebPush();

        $factory = static function (array $auth, array $defaultOptions) use ($fakeWebPush): FakeWebPush {
            // Basic sanity check on auth/defaultOptions shape.
            self::assertArrayHasKey('VAPID', $auth);
            self::assertArrayHasKey('TTL', $defaultOptions);
            return $fakeWebPush;
        };

        $service = new StandardWebPushService($factory);

        $notification = WebPushNotification::create('Test title', 'Test body');

        $raw = json_encode([
            'endpoint' => 'https://example.com/subscription-1',
            'keys' => [
                'p256dh' => 'key1',
                'auth' => 'auth1',
            ],
        ], JSON_THROW_ON_ERROR);

        $subscription = new UserWebPushSubscription(
            'https://example.com/subscription-1',
            '0',
            $raw
        );

        $result = $service->sendWebPushToSubscriptions($notification, [$subscription]);

        $this->assertSame([], $result);
        $this->assertCount(1, $fakeWebPush->sentNotifications);

        /** @var array{SubscriptionInterface, string|null, array<string, mixed>, array<string, mixed>} $first */
        $first = $fakeWebPush->sentNotifications[0];
        /** @var SubscriptionInterface $sentSubscription */
        $sentSubscription = $first[0];
        $payload = $first[1];

        $this->assertSame('https://example.com/subscription-1', $sentSubscription->getEndpoint());
        $this->assertIsString($payload);
        $this->assertStringContainsString('Server side title', $payload);
        $this->assertStringContainsString('Test body', $payload);
    }

    /**
     * @covers \Bristolian\Service\WebPushService\StandardWebPushService::sendWebPushToSubscriptions
     */
    public function test_sendWebPushToSubscriptions_returns_failed_subscriptions_on_error(): void
    {
        $fakeWebPush = new FakeWebPush();
        $fakeWebPush->nextResults = [
            [true, 'OK'],
            [false, 'Failure reason'],
        ];

        $factory = static function (array $auth, array $defaultOptions) use ($fakeWebPush): FakeWebPush {
            return $fakeWebPush;
        };

        $service = new StandardWebPushService($factory);

        $notification = WebPushNotification::create('Title', 'Body');

        $raw1 = json_encode([
            'endpoint' => 'https://example.com/subscription-1',
            'keys' => ['p256dh' => 'key1', 'auth' => 'auth1'],
        ], JSON_THROW_ON_ERROR);
        $raw2 = json_encode([
            'endpoint' => 'https://example.com/subscription-2',
            'keys' => ['p256dh' => 'key2', 'auth' => 'auth2'],
        ], JSON_THROW_ON_ERROR);

        $subscription1 = new UserWebPushSubscription('https://example.com/subscription-1', '0', $raw1);
        $subscription2 = new UserWebPushSubscription('https://example.com/subscription-2', '0', $raw2);

        $failed = $service->sendWebPushToSubscriptions($notification, [$subscription1, $subscription2]);

        $this->assertCount(2, $fakeWebPush->sentNotifications);
        $this->assertSame([$subscription2], $failed);
    }
}

