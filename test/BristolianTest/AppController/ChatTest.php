<?php

declare(strict_types = 1);

namespace BristolianTest\AppController;

use Bristolian\AppController\Chat;
use Bristolian\Repo\ChatMessageRepo\ChatMessageRepo;
use Bristolian\Repo\ChatMessageRepo\FakeChatMessageRepo;
use Bristolian\Response\EndpointAccessedViaGetResponse;
use Bristolian\Response\GetChatRoomMessagesResponse;
use Bristolian\Response\SendChatMessageResponse;
use Bristolian\Session\AppSession;
use BristolianTest\BaseTestCase;
use BristolianTest\Session\FakeAsmSession;
use VarMap\ArrayVarMap;
use VarMap\VarMap;

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

    /**
     * @covers \Bristolian\AppController\Chat::send_message
     */
    public function test_send_message(): void
    {
        $this->setupAppControllerFakes();

        $rawSession = new FakeAsmSession();
        $rawSession->set(AppSession::USER_ID, 'test-user-001');
        $appSession = new AppSession($rawSession);
        $this->injector->share($appSession);

        $varMap = new ArrayVarMap([
            'text' => 'Hello from test',
            'room_id' => 'room-123',
        ]);
        $this->injector->alias(VarMap::class, ArrayVarMap::class);
        $this->injector->share($varMap);

        $result = $this->injector->execute([Chat::class, 'send_message']);

        $this->assertInstanceOf(SendChatMessageResponse::class, $result);
        $this->assertStringContainsString('Hello from test', $result->getBody());
        $this->assertStringContainsString('room-123', $result->getBody());
    }
}
