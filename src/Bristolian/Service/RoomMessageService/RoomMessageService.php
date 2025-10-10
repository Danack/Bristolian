<?php

namespace Bristolian\Service\RoomMessageService;

use Bristolian\Model\ChatMessage;

interface RoomMessageService
{
    public function sendMessage(ChatMessage $message): void;
}
