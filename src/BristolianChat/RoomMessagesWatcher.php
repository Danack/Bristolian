<?php

namespace BristolianChat;

use Amp\Mysql\MysqlConnection;
use Bristolian\Model\Chat\UserChatMessage;
use Monolog\Logger;
use Bristolian\Database\chat_message;

class RoomMessagesWatcher
{
    private int|null $previous_id = null;

    public function __construct(
        private readonly MysqlConnection $mysql_connection,
        private readonly ClientHandler $clientHandler,
        private readonly Logger $logger
    ) {
    }


    /**
     * Initialize previous_id to be the max current message.
     *
     */
    private function initialize_previous_id(): void
    {
        $sql = chat_message::SELECT . " order by id desc limit 1";
        $result = $this->mysql_connection->query($sql);
        $row = $result->fetchRow();

        if ($row === null) {
            $this->logger->info("There are no previous chat messages.");
            $this->previous_id = 0;
            return;
        }

        if (array_key_exists('id', $row) === false) {
            throw new \Exception("Fetching chat_messages failed to find id");
        }

        $this->previous_id = $row['id'];
    }



    private function getMessage(): UserChatMessage|null
    {
        $sql = chat_message::SELECT . " where id > :previous_id order by id asc limit 1";

        $params = ['previous_id' => $this->previous_id];
        $result = $this->mysql_connection->execute(
            $sql,
            $params
        );

        $row = $result->fetchRow();

        if ($row === null) {
            return null;
        }

        return UserChatMessage::fromArray($row);
    }


    public function run(): void
    {
        $this->initialize_previous_id();

        while (true) {
            try {

                $chat_message = $this->getMessage();

                if ($chat_message !== null) {

                    $this->logger->info("Updated previous_id to " . $this->previous_id);
                    $this->previous_id = $chat_message->id;

                    $this->logger->info("Pulled chat message from MySQL: ");

                    send_user_message_to_clients(
                        $chat_message,
                        $this->logger,
                        $this->clientHandler
                    );
                }
            } catch (\Throwable $e) {
                $this->logger->error("Redis loop error: " . $e->getMessage());
            }

            $this->logger->info("RoomMessagesWatcher is looping.");
            // TODO - think about rate limiting.
            \Amp\delay(2);
        }
    }
}