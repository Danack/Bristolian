<?php

namespace Bristolian\AppController;

use Bristolian\Repo\UserSearch\UserSearch;
use Bristolian\Session\AppSession;
use Bristolian\Repo\ProcessorRepo\ProcessorRepo;
use Bristolian\Session\UserSession;
use Bristolian\UserNotifier\UserNotifier;
use SlimDispatcher\Response\JsonResponse;
use SlimDispatcher\Response\RedirectResponse;
use VarMap\VarMap;
use Bristolian\Repo\ProcessorRepo\ProcessType;

class Admin
{
    protected static $processors = [
        ProcessType::daily_system_info->value => "Daily system info",
        ProcessType::email_send->value => "Email send",
        ProcessType::moon_alert->value => "Moon generate alert",
    ];


    public function showNotificationTestPage(): string
    {
        $content = "<h1>Notification test page</h1>";
        $content .= "<div class='notification_test_panel'></div>";

        return $content;
    }

    public function showAdminPage(): string
    {
        $content = "<h1>Admin page</h1>";
        $content .= "<ul>";
        $content .= "<li><a href='/admin/control_processors'>Control processors</a></li>";
        $content .= "<li><a href='/admin/email'>Email status</a></li>";
        $content .= "</ul>";

        return $content;
    }

    public function showEmailPage(): string
    {
        $content = "";

        $content .= "Emails";
        $content .= "<div class='admin_email_panel'></div>";
        return $content;
    }


    public function showProcessorsPage(ProcessorRepo $processorRepo): string
    {
        $content = "<h1>Processors</h1>";
        $content .= "<div class='processors_panel'></div>";

        $processors_states = $processorRepo->getProcessorsStates();

        $content .= "<div class='processors_panel'>";

        foreach ($processors_states as $processor => $info) {
            $content .= "Processor ". $processor . " info ". var_export($info, true);
            $content .= "<hr/>";
        }

//        $content .= var_export($processors_states, true);

        $content .= "</div>";

        $content .= "<div class='processors_panel'>AAAAAHHHRGH</div>";

        $content .= "<table class='processors'>";
        $content .= "<tr><th>Processor</th><th>State</th><th>Last changed</th><th>Change</th></tr>";

        foreach (self::$processors as $processor => $processor_name) {
            $state = "Disabled";
            $class = "disabled";
            $action = "enable";
            $last_changed = "-";

            if (array_key_exists($processor, $processors_states)) {
                $processor_state = $processors_states[$processor];
                $last_changed = $processor_state->updated_at->format("Y-m-d H:i:s");

                if ($processor_state->enabled == true) {
                    $state = "Enabled";
                    $class = "enabled";
                    $action = "disable";
                }
            }

            $button = <<<HTML
<form action='/admin/control_processors' method='post'>
    <input type='hidden' name='processor' value='$processor' />
    <input type='hidden' name='action' value='$action' />
    <input type='submit' value='$action' />
</form>
HTML;

            $content .= "<tr><td>$processor_name</td><td class='$class'>$state</td><td>$last_changed</td><td>$button</td></tr>";
        }

        $content .= "</table>";

        return $content;
    }

    public function updateProcessors(
        ProcessorRepo $processorRepo,
        VarMap $varMap,
        UserSession $appSession
    ): RedirectResponse {

        if ($varMap->has("processor") === false) {
            return new RedirectResponse('/admin/control_processors?message=No processor specified');
        }
        $processor = $varMap->get("processor");
        $processor_type = ProcessType::from($processor);

        if (array_key_exists($processor, self::$processors) === false) {
            return new RedirectResponse('/admin/control_processors?message=Invalid processor specified');
        }

        if ($varMap->has("action") === false) {
            return new RedirectResponse('/admin/control_processors?message=No action specified');
        }
        $action = $varMap->get("action");
        if ($action !== "enable" && $action !== "disable") {
            return new RedirectResponse('/admin/control_processors?message=Invalid action specified');
        }

        $enabled = false;
        if ($action === "enable") {
            $enabled = true;
        }

        $processorRepo->setProcessorEnabled($processor_type, $enabled);
        \error_log("changing {$processor_type->value} to ". (int)$enabled);

        $message = sprintf(
            "/admin/control_processors?message=%s should be %s",
            $processor,
            $action
        );

        return new RedirectResponse($message);
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
