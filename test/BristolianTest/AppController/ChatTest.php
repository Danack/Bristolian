<?php

declare(strict_types = 1);

namespace BristolianTest\AppController;

use Bristolian\AppController\Chat;
use Bristolian\Repo\ChatMessageRepo\ChatMessageRepo;
use Bristolian\Repo\ChatMessageRepo\FakeChatMessageRepo;
use Bristolian\Response\EndpointAccessedViaGetResponse;
use Bristolian\Response\GetChatRoomMessagesResponse;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class ChatTest extends BaseTestCase
{
    public function setup(): void
    {
        parent::setup();
        $this->injector->alias(ChatMessageRepo::class, FakeChatMessageRepo::class);
        $this->injector->share(FakeChatMessageRepo::class);
    }

    /**
     * @covers \Bristolian\AppController\Chat::send_message_get
     */
    public function test_send_message_get(): void
    {
        $result = $this->injector->execute([Chat::class, 'send_message_get']);
        $this->assertInstanceOf(EndpointAccessedViaGetResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Chat::get_test_page
     */
    public function test_get_test_page(): void
    {
        $result = $this->injector->execute([Chat::class, 'get_test_page']);
        $this->assertIsString($result);
        $this->assertStringContainsString('chat_panel', $result);
    }

    /**
     * @covers \Bristolian\AppController\Chat::get_room_messages
     */
    public function test_get_room_messages(): void
    {
        $this->injector->defineParam('room_id', 'test-room-123');

        $result = $this->injector->execute([Chat::class, 'get_room_messages']);
        $this->assertInstanceOf(GetChatRoomMessagesResponse::class, $result);
    }
}
