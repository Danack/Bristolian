<?php

namespace Bristolian\Service\RoomMessageService;

use Bristolian\Model\Chat\UserChatMessage;
use Bristolian\Parameters\ChatMessageParam;

class FakeRoomMessageService implements RoomMessageService
{
    /**
     * @var UserChatMessage[]
     */
    private array $chat_messages = [];

    private static int $nextId = 1;

    public function __construct()
    {
    }

    public function sendMessage(string $user_id, ChatMessageParam $chatMessageParam): UserChatMessage
    {
        $message = new UserChatMessage(
            self::$nextId++,
            $user_id,
            $chatMessageParam->room_id,
            $chatMessageParam->text,
            $chatMessageParam->message_reply_id,
            new \DateTimeImmutable()
        );

        $this->chat_messages[] = $message;

        return $message;
    }

//    public function sendMessageChatMessageAkaOld(UserChatMessage $message): void
//    {
//        $this->chat_messages[] = $message;
//    }

    /**
     * @return UserChatMessage[]
     */
    public function getChatMessages(): array
    {
        return $this->chat_messages;
    }
}
