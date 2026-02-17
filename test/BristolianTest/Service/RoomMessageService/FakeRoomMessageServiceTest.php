<?php

declare(strict_types=1);

namespace BristolianTest\Service\RoomMessageService;

use Bristolian\Parameters\ChatMessageParam;
use Bristolian\Service\RoomMessageService\FakeRoomMessageService;
use BristolianTest\BaseTestCase;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class FakeRoomMessageServiceTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\RoomMessageService\FakeRoomMessageService::__construct
     * @covers \Bristolian\Service\RoomMessageService\FakeRoomMessageService::sendMessage
     * @covers \Bristolian\Service\RoomMessageService\FakeRoomMessageService::getChatMessages
     */
    public function test_sendMessage_stores_message_and_getChatMessages_returns_it(): void
    {
        $service = new FakeRoomMessageService();
        $param = ChatMessageParam::createFromVarMap(new ArrayVarMap([
            'text' => 'Hello',
            'room_id' => 'room_1',
        ]));

        $message = $service->sendMessage('user_1', $param);

        $this->assertSame('user_1', $message->user_id);
        $this->assertSame('room_1', $message->room_id);
        $this->assertSame('Hello', $message->text);

        $messages = $service->getChatMessages();
        $this->assertCount(1, $messages);
        $this->assertSame($message, $messages[0]);
    }

    /**
     * @covers \Bristolian\Service\RoomMessageService\FakeRoomMessageService::sendMessage
     * @covers \Bristolian\Service\RoomMessageService\FakeRoomMessageService::getChatMessages
     */
    public function test_sendMessage_increments_id_and_appends_to_messages(): void
    {
        $service = new FakeRoomMessageService();
        $param1 = ChatMessageParam::createFromVarMap(new ArrayVarMap(['text' => 'First', 'room_id' => 'r1']));
        $param2 = ChatMessageParam::createFromVarMap(new ArrayVarMap(['text' => 'Second', 'room_id' => 'r1']));

        $msg1 = $service->sendMessage('user_1', $param1);
        $msg2 = $service->sendMessage('user_2', $param2);

        $this->assertSame($msg1->id + 1, $msg2->id);
        $messages = $service->getChatMessages();
        $this->assertCount(2, $messages);
        $this->assertSame('First', $messages[0]->text);
        $this->assertSame('Second', $messages[1]->text);
    }
}
