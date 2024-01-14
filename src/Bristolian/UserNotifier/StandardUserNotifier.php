<?php

namespace Bristolian\UserNotifier;

class StandardUserNotifier implements UserNotifier
{
    /**
     * @param string $user
     * @return array<string, string>
     */
    public function notify(string $user): array
    {
        // TODO: Implement notify() method.
        return ['status' => 'ok'];
    }
}
