<?php

namespace Bristolian\Repo\ChatMessageRepo;

use Bristolian\Parameters\ChatMessageParam;
use Bristolian\Model\Chat\UserChatMessage;

interface ChatMessageRepo
{
    public function storeChatMessageForUser(string $user_id, ChatMessageParam $chatMessage): UserChatMessage;

    /**
     * @return UserChatMessage[]
     */
    public function getMessagesForRoom(string $room_id): array;
}
