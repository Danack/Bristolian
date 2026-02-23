<?php

declare(strict_types=1);

namespace Bristolian\ChatMessage;

use Bristolian\Model\Chat\SystemChatMessage;
use Bristolian\Model\Chat\UserChatMessage;

/**
 * Typed payload for data sent to chat clients (replaces unstructured array).
 */
readonly class ChatMessagePayload
{
    private function __construct(
        private ChatType $type,
        private UserChatMessage|null $chat_message,
        private SystemChatMessage|null $system_message,
    ) {
    }

    public static function create_from_user_message(UserChatMessage $chat_message): self
    {
        return new self(
            ChatType::USER_MESSAGE,
            $chat_message,
            null
        );
    }

    public static function create_from_system_message(SystemChatMessage $system_chat_message): self
    {
        return new self(
            ChatType::SYSTEM_MESSAGE,
            null,
            $system_chat_message
        );
    }

    /**
     * @return array{type: string, chat_message?: UserChatMessage, system_message?: SystemChatMessage}
     */
    public function toArray(): array
    {
        $data = [
            'type' => $this->type->value,
        ];
        if ($this->chat_message !== null) {
            $data['chat_message'] = $this->chat_message;
        }
        if ($this->system_message !== null) {
            $data['system_message'] = $this->system_message;
        }
        return $data;
    }
}
