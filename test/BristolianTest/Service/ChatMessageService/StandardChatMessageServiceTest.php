<?php

declare(strict_types=1);

namespace BristolianTest\Service\ChatMessageService;

use Bristolian\Parameters\ChatMessageParam;
use Bristolian\Service\ChatMessageService\StandardChatMessageService;
use Bristolian\Repo\ChatMessageRepo\FakeChatMessageRepo;
use BristolianTest\BaseTestCase;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class StandardChatMessageServiceTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\ChatMessageService\StandardChatMessageService::__construct
     * @covers \Bristolian\Service\ChatMessageService\StandardChatMessageService::handleChatMessage
     */
    public function test_handleChatMessage_stores_message_in_repo(): void
    {
        $repo = new FakeChatMessageRepo();
        $service = new StandardChatMessageService($repo);

        $user_id = 'user-1';
        $room_id = 'room-1';
        $param = ChatMessageParam::createFromVarMap(new ArrayVarMap([
            'text' => 'Hello',
            'room_id' => $room_id,
        ]));

        $service->handleChatMessage($user_id, $param);

        $messages = $repo->getMessagesForRoom($room_id);
        $this->assertCount(1, $messages);
        $this->assertSame($user_id, $messages[0]->user_id);
        $this->assertSame($room_id, $messages[0]->room_id);
        $this->assertSame('Hello', $messages[0]->text);
    }
}
