<?php

namespace Bristolian\Repo\ChatMessageRepo;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use BristolianGenerated\Database\chat_message;
use BristolianGenerated\Database\user_ownership;
use Bristolian\Model\Chat\UserChatMessage;
use Bristolian\Parameters\ChatMessageParam;

interface ChatMessageRepo
{
    #[WritesTable(chat_message::class)]
    #[ReadsTable(chat_message::class)]
    public function storeChatMessageForUser(string $user_id, ChatMessageParam $chatMessage): UserChatMessage;

    #[ReadsTable(user_ownership::class)]
    #[ReadsTable(chat_message::class)]
    #[WritesTable(chat_message::class)]
    public function storeChatMessageForSystem(ChatMessageParam $chatMessage): UserChatMessage;


    /**
     * @return UserChatMessage[]
     */
    #[ReadsTable(chat_message::class)]
    public function getMessagesForRoom(string $room_id): array;
}
