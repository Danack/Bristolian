<?php

declare(strict_types = 1);

namespace Bristolian\Repo\ChatMessageRepo;

use Bristolian\Model\Chat\UserChatMessage;
use Bristolian\Parameters\ChatMessageParam;

/**
 * Fake implementation of ChatMessageRepo for testing.
 */
class FakeChatMessageRepo implements ChatMessageRepo
{
    private int $messageIdCounter = 1;

    /**
     * @var UserChatMessage[]
     */
    private array $messages = [];

    public function storeChatMessageForUser(string $user_id, ChatMessageParam $chatMessage): UserChatMessage
    {
        $messageId = $this->messageIdCounter++;
        $now = new \DateTimeImmutable();

        $userChatMessage = new UserChatMessage(
            id: $messageId,
            user_id: $user_id,
            room_id: $chatMessage->room_id,
            text: $chatMessage->text,
            reply_message_id: $chatMessage->message_reply_id,
            created_at: $now
        );

        $this->messages[$messageId] = $userChatMessage;

        return $userChatMessage;
    }

    /**
     * @return UserChatMessage[]
     */
    public function getMessagesForRoom(string $room_id): array
    {
        $messages = [];
        foreach ($this->messages as $message) {
            if ($message->room_id === $room_id) {
                $messages[] = $message;
            }
        }

        // Sort by id descending (newest first) and limit to 50
        usort($messages, fn($a, $b) => $b->id <=> $a->id);
        
        return array_slice($messages, 0, 50);
    }
}
