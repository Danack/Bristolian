<?php

namespace BristolianChat;

use Bristolian\Model\Chat\UserChatMessage;
use Bristolian\ChatMessage\ChatType;
use Monolog\Logger;

/**
 * This sits and sends test messages.
 */
class ChatSpammer
{
    public function __construct(
        private readonly ClientHandler $clientHandler,
        private readonly Logger $logger
    ) {
    }

    public function run(): void
    {
        // @phpstan-ignore while.alwaysTrue
        while (true) {
            $chat_message = generateFakeChatMessage();

            send_user_message_to_clients(
                $chat_message,
                $this->logger,
                $this->clientHandler
            );

            $this->logger->info("message sent - looping");
            // TODO - think about rate limiting.
            \Amp\delay(20); // Wait a bit before retrying
        }
    }
}
