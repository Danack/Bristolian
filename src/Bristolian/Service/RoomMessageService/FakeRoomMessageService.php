<?php

namespace Bristolian\Service\RoomMessageService;

use Bristolian\Keys\RoomMessageKey;
use Bristolian\Model\ChatMessage;
use Bristolian\ToString;
use Redis;

class FakeRoomMessageService implements RoomMessageService
{
    /**
     * @var ChatMessage[]
     */
    private $chat_messages = [];

    public function __construct(
    ) {
    }

    public function sendMessage(ChatMessage $message): void
    {
        $chat_messages[] = $message;
    }

    public function getChatMessages(): array
    {
        return $this->chat_messages;
    }
}


