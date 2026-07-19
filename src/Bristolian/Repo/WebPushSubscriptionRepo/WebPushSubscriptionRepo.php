<?php

namespace Bristolian\Repo\WebPushSubscriptionRepo;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use Bristolian\Database\user_webpush_subscription;
use Bristolian\Parameters\WebPushSubscriptionParams;

interface WebPushSubscriptionRepo
{
    /**
     * @param string $username
     * @return \Bristolian\Model\Types\UserWebPushSubscription[]
     */
    #[ReadsTable(user_webpush_subscription::class)]
    public function getUserSubscriptions(string $username): array;

    #[WritesTable(user_webpush_subscription::class)]
    public function save(
        string                    $user_id,
        WebPushSubscriptionParams $webPushSubscriptionParam,
        string                    $raw
    ): void;
}
