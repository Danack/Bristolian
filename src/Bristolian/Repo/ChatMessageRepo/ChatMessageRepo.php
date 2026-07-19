<?php

namespace Bristolian\Repo\ChatMessageRepo;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use Bristolian\Database\chat_message;
use Bristolian\Database\user_ownership;
use Bristolian\Model\Chat\UserChatMessage;
use Bristolian\Parameters\ChatMessageParam;

interface ChatMessageRepo
{
    #[WritesTable(chat_message::class)]
    #[ReadsTable(chat_message::class)]
    public function storeChatMessageForUser(string $user_id, ChatMessageParam $chatMessage): UserChatMessage;

    #[ReadsTable(user_ownership::class)]
    public function storeChatMessageForSystem(ChatMessageParam $chatMessage): UserChatMessage;


    /**
     * @return UserChatMessage[]
     */
    #[ReadsTable(chat_message::class)]
    public function getMessagesForRoom(string $room_id): array;
}
