<?php

namespace Bristolian\Repo\ChatMessageRepo;

use Bristolian\Parameters\ChatMessageParam;
use Bristolian\Model\ChatMessage;

interface ChatMessageRepo
{
    public function storeChatMessageForUser(string $user_id, ChatMessageParam $chatMessage): ChatMessage;
}
