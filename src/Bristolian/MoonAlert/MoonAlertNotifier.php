<?php

namespace Bristolian\MoonAlert;

interface MoonAlertNotifier
{
    public function notifyUsers(array $email_addresses, string $moon_info);
}
