<?php

namespace Bristolian\Service\RoomMessageService;

use Bristolian\ChatMessage\StandardMessage;

interface RoomMessageService
{
    public function sendMessage(StandardMessage $message): void;
}
