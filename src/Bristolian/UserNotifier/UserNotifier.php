<?php

namespace Bristolian\UserNotifier;

interface UserNotifier
{
    /**
     * @param string $user
     * @return array<string, string>
     */
    public function notify(string $user): array;
}
