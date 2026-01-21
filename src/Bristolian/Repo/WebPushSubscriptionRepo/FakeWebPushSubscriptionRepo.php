<?php

declare(strict_types = 1);

namespace Bristolian\Repo\WebPushSubscriptionRepo;

use Bristolian\Model\Types\UserWebPushSubscription;
use Bristolian\Parameters\WebPushSubscriptionParams;

/**
 * Fake implementation of WebPushSubscriptionRepo for testing.
 */
class FakeWebPushSubscriptionRepo implements WebPushSubscriptionRepo
{
    /**
     * @var array<string, array{endpoint: string, expiration_time: string|null, raw: string}>
     * Keyed by user_id, then by endpoint
     */
    private array $subscriptions = [];

    /**
     * @param string $username
     * @return UserWebPushSubscription[]
     */
    public function getUserSubscriptions(string $username): array
    {
        if (!isset($this->subscriptions[$username])) {
            return [];
        }

        $results = [];
        foreach ($this->subscriptions[$username] as $subscription) {
            $results[] = new UserWebPushSubscription(
                $subscription['endpoint'],
                $subscription['expiration_time'] ?? '',
                $subscription['raw']
            );
        }

        return $results;
    }

    /**
     * @throws UserConstraintFailedException
     */
    public function save(
        string                    $user_id,
        WebPushSubscriptionParams $webPushSubscriptionParam,
        string                    $raw
    ): void {
        // Check for duplicate endpoint for same user (simulating unique constraint)
        if (isset($this->subscriptions[$user_id][$webPushSubscriptionParam->endpoint])) {
            throw new UserConstraintFailedException(
                "Failed to insert, user constraint errored.",
                23000,
                null
            );
        }

        if (!isset($this->subscriptions[$user_id])) {
            $this->subscriptions[$user_id] = [];
        }

        $this->subscriptions[$user_id][$webPushSubscriptionParam->endpoint] = [
            'endpoint' => $webPushSubscriptionParam->endpoint,
            'expiration_time' => $webPushSubscriptionParam->expiration_time,
            'raw' => $raw,
        ];
    }
}
