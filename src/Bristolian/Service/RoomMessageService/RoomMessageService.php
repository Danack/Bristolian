<?php

namespace Bristolian\Service\RoomMessageService;

use Bristolian\Model\Chat\UserChatMessage;
use Bristolian\Parameters\ChatMessageParam;

interface RoomMessageService
{
    public function sendMessage(string $user_id, ChatMessageParam $chatMessageParam): UserChatMessage;
}
