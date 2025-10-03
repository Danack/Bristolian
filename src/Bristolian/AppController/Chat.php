<?php

namespace Bristolian\AppController;

use Bristolian\Repo\ChatMessageRepo\ChatMessageRepo;
use Bristolian\Session\AppSession;
use Bristolian\Parameters\ChatMessageParam;
use SlimDispatcher\Response\JsonNoCacheResponse;
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
            'room_id' => '019980fb-8a39-7223-9402-e9d7973cd7f7'
        ];

        $widget_json = json_encode_safe($props);
        $widget_data = htmlspecialchars($widget_json);


        $html = <<< HTML
<div>
    <h3>Chat test</h3>
    <div class="chat_panel" data-widgety_json="$widget_data">
    </div>
</div>

HTML;

        return $html;
    }



    public function get_room_messages(
        ChatMessageRepo $chatMessageRepo,
        string $room_id
    ): void {
    }


    public function send_message(
        ChatMessageRepo $chatMessageRepo,
        AppSession $appSession,
        VarMap $varMap
    ): JsonNoCacheResponse {

        $messageParams = ChatMessageParam::createFromVarMap($varMap);

        $chat_message = $chatMessageRepo->storeChatMessageForUser(
            $appSession->getUserId(),
            $messageParams
        );

        return new JsonNoCacheResponse(['data' => $chat_message]);
    }
}
