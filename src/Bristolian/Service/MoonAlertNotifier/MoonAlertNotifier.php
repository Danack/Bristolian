<?php

namespace Bristolian\Service\MoonAlertNotifier;

interface MoonAlertNotifier
{
    public function notifyRegisteredUsers(string $mooninfo): void;
}
