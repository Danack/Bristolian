<?php

namespace Bristolian\AppController;

use Bristolian\DataType\WebPushSubscriptionParam;
use Bristolian\JsonInput\JsonInput;
use Bristolian\Repo\WebPushSubscriptionRepo\WebPushSubscriptionRepo;
use Bristolian\Session\UserSession;
use Minishlink\WebPush\VAPID;
use SlimDispatcher\Response\JsonResponse;

class Notifications
{
    public function generate_keys(): string
    {
        $content = "Here are some keys that can be used for webpushes.";
        $content .= var_export(VAPID::createVapidKeys(), true);
        // store the keys afterwards
        return $content;
    }

    public function save_subscription_get(): string
    {
        return "You probably meant to do a POST to this endpoint.";
    }

    public function save_subscription(
        JsonInput $jsonInput,
        UserSession $appSession,
        WebPushSubscriptionRepo $webPushSubscriptionRepo
    ): JsonResponse {

//        if ($appSession->isLoggedIn() !== true) {
//            $data = ['not logged in' => true];
//            return new JsonResponse($data, [], 400);
//        }

        [$webPushSubscriptionParam, $validation_problems] =
        WebPushSubscriptionParam::createOrErrorFromArray($jsonInput->getData());

        if ($errorResponse = createErrorJsonResponse($validation_problems)) {
            return $errorResponse;
        }

        $webPushSubscriptionRepo->save(
            $appSession->getUserId(),
            $webPushSubscriptionParam,
            $payload = @file_get_contents("php://input")
        );

        return new JsonResponse(['success' => true]);
    }
}
