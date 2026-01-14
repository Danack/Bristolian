<?php

namespace Bristolian\Service\WebPushService;

use Bristolian\Model\Types\UserWebPushSubscription;
use Bristolian\Model\Types\WebPushNotification;

interface WebPushService
{
    /**
     * @param WebPushNotification $webPushNotification
     * @param UserWebPushSubscription[] $userWebPushSubscriptions
     * @return \Bristolian\Model\Types\UserWebPushSubscription[]
     */
    public function sendWebPushToSubscriptions(
        WebPushNotification $webPushNotification,
        array $userWebPushSubscriptions
    );
}
