<?php

namespace Bristolian\AppController;

use Bristolian\Parameters\WebPushSubscriptionParams;
use Bristolian\JsonInput\JsonInput;
use Bristolian\Repo\WebPushSubscriptionRepo\WebPushSubscriptionRepo;
use Bristolian\Response\EndpointAccessedViaGetResponse;
use Bristolian\Response\SuccessResponse;
use Bristolian\Session\UserSession;
use Minishlink\WebPush\VAPID;
use Bristolian\Response\ValidationErrorResponse;

class Notifications
{
    public function generate_keys(): string
    {
        $content = "Here are some keys that can be used for webpushes.";
        $content .= var_export(VAPID::createVapidKeys(), true);
        // store the keys afterwards
        return $content;
    }

    public function save_subscription_get(): EndpointAccessedViaGetResponse
    {
        return new EndpointAccessedViaGetResponse();
    }

    public function save_subscription(
        JsonInput $jsonInput,
        UserSession $appSession,
        WebPushSubscriptionRepo $webPushSubscriptionRepo
    ): SuccessResponse|ValidationErrorResponse {

//        if ($appSession->isLoggedIn() !== true) {
//            $data = ['not logged in' => true];
//            return new JsonResponse($data, [], 400);
//        }

        [$webPushSubscriptionParam, $validation_problems] =
          WebPushSubscriptionParams::createOrErrorFromArray($jsonInput->getData());

        if (count($validation_problems)) {
            return ValidationErrorResponse::fromProblems($validation_problems);
        }

        $webPushSubscriptionRepo->save(
            $appSession->getUserId(),
            $webPushSubscriptionParam,
            $payload = @file_get_contents("php://input")
        );

        return new SuccessResponse();
    }
}
