<?php

namespace Bristolian\Service\WebPushService;

use Bristolian\Model\Types\UserWebPushSubscription;
use Bristolian\Model\Types\WebPushNotification;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class StandardWebPushService implements WebPushService
{
    /**
     * @var callable
     */
    private $webPushFactory;

    /**
     * @param callable|null $webPushFactory factory that receives auth and default options
     *                                      and returns a Minishlink\WebPush\WebPush instance
     */
    public function __construct(?callable $webPushFactory = null)
    {
        // @codeCoverageIgnoreStart
        $this->webPushFactory = $webPushFactory ?? static function (array $auth, array $defaultOptions): WebPush {
       
            return new WebPush($auth, $defaultOptions);
        };
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param WebPushNotification $webPushNotification
     * @param UserWebPushSubscription[] $userWebPushSubscriptions
     * @return UserWebPushSubscription[] Subscriptions that failed to send
     * @throws \ErrorException
     */
    public function sendWebPushToSubscriptions(
        WebPushNotification $webPushNotification,
        array $userWebPushSubscriptions
    ): array {
        $auth = [
            'VAPID' => [
                'subject' => 'https://bristolian.org', // can be a mailto:
                'publicKey' => getVapidPublicKey(),
                'privateKey' => getVapidPrivateKey(),
            ],
        ];

        // Urgency can be either "very-low", "low", "normal", or "high".
        $defaultOptions = [
            'TTL' => 300, // defaults to 4 weeks
            'urgency' => 'normal', // protocol defaults to "normal". (very-low, low, normal, or high)
            'topic' => 'newEvent', // Max. 32 characters from the URL or filename-safe Base64 characters sets
            'batchSize' => 200, // defaults to 1000
        ];

        /** @var WebPush $webPush */
        $webPush = ($this->webPushFactory)($auth, $defaultOptions);

        $failedSubscriptions = [];

        $notification_data = [
            'title' => "Server side title",
            'body' => $webPushNotification->getBody(),
            'vibrate' => [500,110,500,110,450,110,200,110,170,40,450,110,200,110,170,40,500],
            'sound' => "/sounds/meow.mp3",
            'data' => [
                'url' => '/tools'
            ]
        ];

        /**
         * send one notification and flush directly
         */
        foreach ($userWebPushSubscriptions as $userWebPushSubscription) {
            $associativeArray = json_decode_safe($userWebPushSubscription->getRaw());
            $subscription = Subscription::create($associativeArray);
            $report = $webPush->sendOneNotification(
                $subscription,
                json_encode_safe($notification_data)
            );

            if ($report->isSuccess() !== true) {
                $failedSubscriptions[] = $userWebPushSubscription;
            }
        }

        return $failedSubscriptions;
    }
}
