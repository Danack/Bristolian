<?php

namespace BristolianChat;

use Bristolian\Keys\RoomMessageKey;
use Bristolian\Model\Chat\UserChatMessage;
use Amp\Mysql\MysqlConnection;
use Monolog\Logger;


class RoomFilesWatcher
{

    public function __construct(
        private readonly MysqlConnection $mysql_connection,
        private readonly ClientHandler $clientHandler,
        private readonly Logger $logger
    ) {
    }


    public function run(): void
    {
        while (true) {
            try {
//                $list = $this->redis->getList(RoomMessageKey::getAbsoluteKeyName());
//                $item = $list->popHeadBlocking();
                // Get the max file id from the list of files.
                // If it's greater than max last stored


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