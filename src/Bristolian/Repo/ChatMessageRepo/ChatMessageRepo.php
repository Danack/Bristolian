<?php

namespace Bristolian\Repo\ChatMessageRepo;

use Bristolian\Parameters\ChatMessageParam;

interface ChatMessageRepo
{
    public function storeChatMessageForUser(string $user_id, ChatMessageParam $chatMessage);
}
