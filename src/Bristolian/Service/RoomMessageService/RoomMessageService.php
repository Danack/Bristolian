<?php

namespace Bristolian\Service\RoomMessageService;

use Bristolian\Model\ChatMessage;
use Bristolian\Parameters\ChatMessageParam;

interface RoomMessageService
{
    public function sendMessageChatMessageAkaOld(ChatMessage $message): void;

    public function sendMessage(string $user_id, ChatMessageParam $chatMessageParam): ChatMessage;
}
