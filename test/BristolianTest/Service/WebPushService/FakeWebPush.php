<?php

declare(strict_types=1);

namespace BristolianTest\Service\WebPushService;

use GuzzleHttp\Psr7\Request;
use Minishlink\WebPush\MessageSentReport;
use Minishlink\WebPush\SubscriptionInterface;
use Minishlink\WebPush\WebPush;

/**
 * Test double for Minishlink\WebPush\WebPush that never performs HTTP requests.
 */
class FakeWebPush extends WebPush
{
    /**
     * @var array<int, array{SubscriptionInterface, string|null, array<string, mixed>, array<string, mixed>}>
     */
    public array $sentNotifications = [];

    /**
     * @var array<int, array{bool, string}>
     */
    public array $nextResults = [];

    public function __construct()
    {
        // Do not call parent constructor; tests only rely on sendOneNotification.
    }

    /**
     * @param array<string, mixed> $options
     * @param array<string, mixed> $auth
     */
    public function sendOneNotification(
        SubscriptionInterface $subscription,
        ?string $payload = null,
        array $options = [],
        array $auth = []
    ): MessageSentReport {
        $this->sentNotifications[] = [$subscription, $payload, $options, $auth];

        if ($this->nextResults !== []) {
            [$success, $reason] = array_shift($this->nextResults);
        } else {
            $success = true;
            $reason = 'OK';
        }

        $request = new Request('POST', 'https://example.com/web-push-test');

        return new MessageSentReport($request, null, $success, $reason);
    }
}
