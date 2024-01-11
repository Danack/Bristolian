<?php

namespace Bristolian\UserNotifier;

interface UserNotifier
{
    public function notify(string $user);
}
