<?php

namespace Bristolian\Service\ChatMessageService;

use Bristolian\Parameters\ChatMessageParam;
use Bristolian\Repo\ChatMessageRepo\ChatMessageRepo;

class StandardChatMessageService implements ChatMessageService
{


    public function __construct(
        private ChatMessageRepo $chatMessageRepo
    ) {
    }

    public function handleChatMessage(string $user_id, ChatMessageParam $chatMessage): void
    {

        $message_id = $this->chatMessageRepo->storeChatMessageForUser(
            $user_id,
            $chatMessage
        );
    }
}
