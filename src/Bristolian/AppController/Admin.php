<?php

namespace Bristolian\AppController;

use Bristolian\AppSession;
use SlimDispatcher\Response\JsonResponse;
use Bristolian\Repo\UserSearch\UserSearch;
use SlimDispatcher\Response\RedirectResponse;
use VarMap\VarMap;
use Bristolian\UserNotifier\UserNotifier;

class Admin
{
    public function showNotificationTestPage(): string
    {
        $content = "<h1>Notification test page</h1>";
        $content .= "<div class='notification_test_panel'></div>";

        return $content;
    }

    public function ping_user(
        AppSession $appSession,
        VarMap $varMap,
        UserNotifier $user_notifier
    ): JsonResponse {
        if ($appSession->isLoggedIn()) {
            return new JsonResponse([]);
        }

        $user = $varMap->getWithDefault("user", null);
        if ($user === null) {
            return new JsonResponse([]);
        }

        $result = $user_notifier->notify($user);
        return new JsonResponse($result);
    }


    public function search_users(
        UserSearch $userSearch,
        VarMap $varMap
    ): JsonResponse {
        // TODO - convert to DataType
        $user_search = $varMap->getWithDefault("user_search", null);
        if ($user_search === null) {
            return new JsonResponse([]);
        }

        $data = $userSearch->searchUsernamesByPrefix($user_search);

        return new JsonResponse($data);
    }
}
