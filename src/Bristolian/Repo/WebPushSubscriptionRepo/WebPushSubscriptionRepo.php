<?php

namespace Bristolian\Repo\WebPushSubscriptionRepo;

use Bristolian\Parameters\WebPushSubscriptionParams;

interface WebPushSubscriptionRepo
{
    /**
     * @param string $username
     * @return \Bristolian\Model\UserWebPushSubscription[]
     */
    public function getUserSubscriptions(string $username): array;

    public function save(
        string                    $user_id,
        WebPushSubscriptionParams $webPushSubscriptionParam,
        string                    $raw
    ): void;
}
