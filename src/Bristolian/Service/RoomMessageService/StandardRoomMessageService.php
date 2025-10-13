<?php

namespace Bristolian\Service\RoomMessageService;

use Bristolian\Keys\RoomMessageKey;
use Bristolian\Model\ChatMessage;
use Bristolian\Parameters\ChatMessageParam;
use Bristolian\Repo\ChatMessageRepo\ChatMessageRepo;
use Bristolian\ToString;
use Redis;

class StandardRoomMessageService implements RoomMessageService
{
    public function __construct(
        private readonly Redis $redis,
        private ChatMessageRepo $chatMessageRepo,
    ) {
    }


    public function sendMessage(string $user_id, ChatMessageParam $chatMessageParam): ChatMessage
    {
        $chat_message = $this->chatMessageRepo->storeChatMessageForUser(
            $user_id,
            $chatMessageParam
        );

        $this->redis->rPush(
            RoomMessageKey::getAbsoluteKeyName(),
            $chat_message->toString()
        );

        return $chat_message;
    }


    public function sendMessageChatMessageAkaOld(ChatMessage $message): void
    {
        $this->redis->rPush(
            RoomMessageKey::getAbsoluteKeyName(),
            $message->toString()
        );
    }
}
