<?php

namespace Bristolian\Service\WebPushService;

use Bristolian\Model\UserWebPushSubscription;
use Bristolian\Model\WebPushNotification;

interface WebPushService
{
    /**
     * @param WebPushNotification $webPushNotification
     * @param UserWebPushSubscription[] $userWebPushSubscriptions
     * @return \Bristolian\Model\UserWebPushSubscription[]
     */
    public function sendWebPushToSubscriptions(
        WebPushNotification $webPushNotification,
        array $userWebPushSubscriptions
    );
}
