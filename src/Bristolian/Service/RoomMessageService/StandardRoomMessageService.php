<?php

namespace Bristolian\Service\RoomMessageService;

//use Bristolian\Keys\RoomMessageKey;
use Bristolian\Model\Chat\UserChatMessage;
use Bristolian\Parameters\ChatMessageParam;
use Bristolian\Repo\ChatMessageRepo\ChatMessageRepo;
use Bristolian\Repo\UserRepo\UserRepo;

class StandardRoomMessageService implements RoomMessageService
{
    public function __construct(
        private UserRepo $userRepo,
        private ChatMessageRepo $chatMessageRepo,
    ) {
    }

    public function sendRoomMessage(ChatMessageParam $chatMessageParam): UserChatMessage
    {
        $room_user_info = $this->userRepo->getRoomUserForRoom($chatMessageParam->room_id);

        return $this->chatMessageRepo->storeChatMessageForUser(
            $room_user_info->user_id,
            $chatMessageParam
        );
    }


    public function sendSystemMessage(ChatMessageParam $chatMessageParam): UserChatMessage
    {
        return $this->chatMessageRepo->storeChatMessageForSystem($chatMessageParam);
    }

    public function sendMessage(string $user_id, ChatMessageParam $chatMessageParam): UserChatMessage
    {
        // TODO - some security?

        $chat_message = $this->chatMessageRepo->storeChatMessageForUser(
            $user_id,
            $chatMessageParam
        );

//        $this->redis->rPush(
//            RoomMessageKey::getAbsoluteKeyName(),
//            $chat_message->toString()
//        );

        return $chat_message;
    }
}
