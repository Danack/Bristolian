<?php

namespace Bristolian\Service\ChatMessageService;

use Bristolian\Parameters\ChatMessageParam;

/**
 * Receives messages, passes them to repo code to be saved, and also
 * distributes notifications.
 */
interface ChatMessageService
{
    public function handleChatMessage(string $user_id, ChatMessageParam $chatMessage);
}
