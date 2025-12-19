<?php

namespace Bristolian\Service\RoomMessageService;

use Bristolian\Keys\RoomMessageKey;
use Bristolian\Model\Chat\UserChatMessage;
use Bristolian\ToString;
use Redis;

class FakeRoomMessageService implements RoomMessageService
{
    /**
     * @var UserChatMessage[]
     */
    private $chat_messages = [];

    public function __construct(
    ) {
    }

    public function sendMessage(UserChatMessage $message): void
    {
        $chat_messages[] = $message;
    }

    public function getChatMessages(): array
    {
        return $this->chat_messages;
    }
}


