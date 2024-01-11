<?php

namespace Bristolian\UserNotifier;

class StandardUserNotifier implements UserNotifier
{
    public function notify(string $user)
    {
        // TODO: Implement notify() method.
        return ['status' => 'ok'];
    }
}
