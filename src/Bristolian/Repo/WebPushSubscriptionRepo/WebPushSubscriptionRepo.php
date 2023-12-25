<?php

namespace Bristolian\Repo\WebPushSubscriptionRepo;

use Bristolian\Model\User;
use Bristolian\DataType\WebPushSubscriptionParam;

interface WebPushSubscriptionRepo
{
    /**
     * @param string $username
     * @return \Bristolian\Model\UserWebPushSubscription[]
     */
    public function getUserSubscriptions(string $username): array;

    public function save(
        string $user_id,
        WebPushSubscriptionParam $webPushSubscriptionParam,
        string $raw
    ): void;
}
