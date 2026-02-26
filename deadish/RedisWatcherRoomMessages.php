<?php

namespace deadish;

use Amp\Redis\RedisClient;
use Bristolian\Keys\RoomMessageKey;
use Bristolian\Model\Chat\UserChatMessage;
use BristolianChat\ClientHandler;
use Monolog\Logger;


/**
 * This sits watching for messages for rooms, and then dispatches them to clients.
 */
class RedisWatcherRoomMessages
{
    public function __construct(
        private readonly RedisClient $redis,
        private readonly ClientHandler $clientHandler,
        private readonly Logger $logger
    ) {
    }

    public function run(): void
    {
        while (true) {
            try {
                // This code needs to go away. We should just watch the database for new messages.
                $list = $this->redis->getList(RoomMessageKey::getAbsoluteKeyName());
                $item = $list->popHeadBlocking();

                if ($item !== null) {
                    $this->logger->info("Received event from Redis: " . $item);
                    $chat_message = UserChatMessage::fromString($item);

                    send_user_message_to_clients(
                        $chat_message,
                        $this->logger,
                        $this->clientHandler
                    );
                }
            } catch (\Throwable $e) {
                $this->logger->error("Redis loop error: " . $e->getMessage());
            }

            $this->logger->info("RedisWatcherRoomMessages is looping.");
            // TODO - think about rate limiting.
            \Amp\delay(0.5); // Wait a bit before retrying
        }
    }
}
