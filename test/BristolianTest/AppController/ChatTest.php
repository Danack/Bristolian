<?php

declare(strict_types = 1);

namespace BristolianTest\AppController;

use Bristolian\AppController\Chat;
use Bristolian\Parameters\ChatMessageParam;
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
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\AppController\Chat::class, 'get_room_messages')]
#[CoversMethod(\Bristolian\AppController\Chat::class, 'get_test_page')]
#[CoversMethod(\Bristolian\AppController\Chat::class, 'send_message')]
#[CoversMethod(\Bristolian\AppController\Chat::class, 'send_message_get')]

class ChatTest extends BaseTestCase
{
    public function setup(): void
    {
        parent::setup();
        $this->injector->alias(ChatMessageRepo::class, FakeChatMessageRepo::class);
        $this->injector->share(FakeChatMessageRepo::class);
    }

    public function test_send_message_get(): void
    {
        $result = $this->injector->execute([Chat::class, 'send_message_get']);
        $this->assertInstanceOf(EndpointAccessedViaGetResponse::class, $result);
    }

    public function test_get_test_page(): void
    {
        $result = $this->injector->execute([Chat::class, 'get_test_page']);
        $this->assertIsString($result);
        $this->assertStringContainsString('chat_panel', $result);
    }

    public function test_get_room_messages(): void
    {
        $roomId = 'test-room-123';
        $this->injector->defineParam('room_id', $roomId);

        $chatMessageRepo = $this->injector->make(FakeChatMessageRepo::class);
        $storeParam = ChatMessageParam::createFromVarMap(new ArrayVarMap([
            'text' => 'Plain **markdown** line',
            'room_id' => $roomId,
        ]));
        $chatMessageRepo->storeChatMessageForUser('seed-user', $storeParam);

        $result = $this->injector->execute([Chat::class, 'get_room_messages']);
        $this->assertInstanceOf(GetChatRoomMessagesResponse::class, $result);
        $this->assertStringContainsString('Plain', $result->getBody());
    }

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
