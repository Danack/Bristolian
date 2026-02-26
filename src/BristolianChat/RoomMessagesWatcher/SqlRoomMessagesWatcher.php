<?php

namespace BristolianChat\RoomMessagesWatcher;

use Amp\Mysql\MysqlConnection;
use Bristolian\Database\chat_message;
use Bristolian\Model\Generated\ChatMessage;
use Monolog\Logger;

class SqlRoomMessagesWatcher implements RoomMessagesWatcher
{
    public function __construct(
        private readonly MysqlConnection $mysql_connection,
        private readonly Logger $logger,
    ) {
    }

    public function getInitialPreviousId(): int
    {
        $sql = chat_message::SELECT . " order by id desc limit 1";
        $result = $this->mysql_connection->query($sql);
        $row = $result->fetchRow();

        if ($row === null) {
            // @codeCoverageIgnoreStart
            $this->logger->info("There are no previous chat messages.");
            return 0;
            // @codeCoverageIgnoreEnd
        }

        return $row['id'];
    }

    public function getNextChatMessageAfter(int $previousId): ChatMessage|null
    {
        $sql = chat_message::SELECT . " where id > :previous_id order by id asc limit 1";
        $params = ['previous_id' => $previousId];
        $result = $this->mysql_connection->execute($sql, $params);
        $row = $result->fetchRow();

        if ($row === null) {
            return null;
        }

        if (is_string($row['created_at'] ?? null)) {
            $row['created_at'] = new \DateTimeImmutable($row['created_at']);
        }

        return ChatMessage::fromArray($row);
    }
}
