<?php

namespace Bristolian\Service\WebPushService;

use Bristolian\Model\WebPushNotification;
use Bristolian\Model\UserWebPushSubscription;

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
