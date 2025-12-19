<?php

namespace Bristolian\AppController;

use Bristolian\Repo\ChatMessageRepo\ChatMessageRepo;
use Bristolian\Session\AppSession;
use Bristolian\Parameters\ChatMessageParam;
use SlimDispatcher\Response\JsonNoCacheResponse;
use Bristolian\Service\RoomMessageService\RoomMessageService;
use Bristolian\App;
use VarMap\VarMap;

class Chat
{
    public function send_message_get(): JsonNoCacheResponse
    {
        return new JsonNoCacheResponse(['this is meant to be a post end point.']);
    }

    public function get_test_page(): string
    {
        $props = [
            'room_id' => App::ROOM_ID_DEBUG
        ];

        $widget_json = json_encode_safe($props);
        $widget_data = htmlspecialchars($widget_json);


        $html = <<< HTML
<div>
    <div class="chat_panel" data-widgety_json="$widget_data">
    </div>
</div>

HTML;

        return $html;
    }



    public function get_room_messages(
        ChatMessageRepo $chatMessageRepo,
        string $room_id
    ): JsonNoCacheResponse {
        $messages = $chatMessageRepo->getMessagesForRoom($room_id);

        // Convert messages to array format for JSON response
        $messageData = [];
        foreach ($messages as $message) {
            $messageData[] = [
                'id' => $message->id,
                'user_id' => $message->user_id,
                'room_id' => $message->room_id,
                'text' => $message->text,
                'reply_message_id' => $message->reply_message_id,
                'created_at' => $message->created_at->format('Y-m-d H:i:s')
            ];
        }

        return new JsonNoCacheResponse(['messages' => $messageData]);
    }


    public function send_message(
        RoomMessageService $roomMessageService,
        AppSession $appSession,
        VarMap $varMap
    ): JsonNoCacheResponse {

        $messageParams = ChatMessageParam::createFromVarMap($varMap);

        $chat_message = $roomMessageService->sendMessage(
            $appSession->getUserId(),
            $messageParams
        );

        return new JsonNoCacheResponse(['data' => $chat_message]);
    }
}
