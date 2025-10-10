<?php

namespace BristolianChat;

use Amp\Redis\RedisClient;
use Bristolian\ChatMessage\ChatType;
use Bristolian\Keys\RoomMessageKey;
use Monolog\Logger;
use Bristolian\Model\ChatMessage;

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
                $list = $this->redis->getList(RoomMessageKey::getAbsoluteKeyName());
                $item = $list->popHeadBlocking();

                if ($item !== null) {
                    $this->logger->info("Received event from Redis: " . $item);
                    $chat_message = ChatMessage::fromString($item);

                    send_message_to_clients(
                        $chat_message,
                        $this->logger,
                        $this->clientHandler
                    );
                }
            } catch (\Throwable $e) {
                $this->logger->error("Redis loop error: " . $e->getMessage());
            }

            $this->logger->info("looping");
            // TODO - think about rate limiting.
            \Amp\delay(0.5); // Wait a bit before retrying
        }
    }
}
