<?php

namespace Bristolian\Repo\ChatMessageRepo;

use Bristolian\Parameters\ChatMessageParam;
use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Model\Chat\UserChatMessage;
use Bristolian\Database\chat_message;

class PdoChatMessageRepo implements ChatMessageRepo
{
    public function __construct(private PdoSimple $pdo)
    {
    }

    public function storeChatMessageForUser(string $user_id, ChatMessageParam $chatMessage): UserChatMessage
    {
        $sql_insert = chat_message::INSERT;

        $params = [
            ':reply_message_id' => $chatMessage->message_reply_id,
            ':room_id' => $chatMessage->room_id,
            ':user_id' => $user_id,
            ':text' => $chatMessage->text
        ];

        $message_id = $this->pdo->insert($sql_insert, $params);

        $sql_select = chat_message::SELECT;
        $sql_select .= " where id = :message_id";

        return $this->pdo->fetchOneAsObject(
            $sql_select,
            [':message_id' => $message_id],
            UserChatMessage::class
        );
    }

    public function getMessagesForRoom(string $room_id): array
    {
        $sql = chat_message::SELECT;
        $sql .= " where room_id = :room_id ORDER BY id DESC LIMIT 50";

        return $this->pdo->fetchAllAsObjectConstructor(
            $sql,
            [':room_id' => $room_id],
            UserChatMessage::class
        );
    }
}
