<?php

namespace Bristolian\AppController;

use Bristolian\UserSession;
use Bristolian\AppSession;
use Bristolian\JsonInput\JsonInput;
use DataType\Create\CreateOrErrorFromJson;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\VAPID;
use SlimDispatcher\Response\JsonResponse;
use Bristolian\DataType\WebPushSubscriptionParam;
use Bristolian\Repo\WebPushSubscriptionRepo\WebPushSubscriptionRepo;

class Notifications
{
    function generate_keys(): string
    {
        $content = "Here are some keys that can be used for webpushes.";
        $content .= var_export(VAPID::createVapidKeys(), true);
        // store the keys afterwards
        return $content;
    }

    function save_subscription(
        JsonInput $jsonInput,
        UserSession $appSession,
        WebPushSubscriptionRepo $webPushSubscriptionRepo
    ): JsonResponse {

        if ($appSession->isLoggedIn() !== true) {
            $data = ['not logged in' => true];
            return new JsonResponse($data, [], 400);
        }

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
