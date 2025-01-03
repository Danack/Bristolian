<?php

namespace Bristolian\CliController;

use Bristolian\Model\WebPushNotification;
use Bristolian\Repo\AdminRepo\AdminRepo;
use Bristolian\Repo\WebPushSubscriptionRepo\WebPushSubscriptionRepo;
use Bristolian\Service\WebPushService\WebPushService;

/**
 * Placeholder code for testing webpushes.
 * @codeCoverageIgnore
 */
class Debug
{
    public function hello(): void
    {
        echo "Hello.";
    }

    public function send_webpush(
        string $email_address,
        string $message,
        AdminRepo $adminRepo,
        WebPushSubscriptionRepo $webPushSubscriptionRepo,
        WebPushService $webPushService
    ): void {
        $webPushNotification = WebPushNotification::create('Test message', $message);

        echo "Need to send to $email_address the message '$message'.\n";

        $user_id = $adminRepo->getAdminUserId($email_address);

        if ($user_id === null) {
            echo "User $email_address not found.";
            return;
        }

        $userWebPushSubscriptions = $webPushSubscriptionRepo->getUserSubscriptions($user_id);

        if (count($userWebPushSubscriptions) === 0) {
            echo "User has no Web Push Subscriptions.\n";
            return;
        }

        $webPushService->sendWebPushToSubscriptions(
            $webPushNotification,
            $userWebPushSubscriptions
        );
    }
}
