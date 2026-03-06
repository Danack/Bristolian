<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\ChatMessageRepo;

use Bristolian\Model\Chat\UserChatMessage;
use Bristolian\Parameters\ChatMessageParam;
use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Repo\ChatMessageRepo\ChatMessageRepo;
use Bristolian\Repo\ChatMessageRepo\PdoChatMessageRepo;
use BristolianTest\Repo\DbTransactionIsolation;
use BristolianTest\Support\HasTestWorld;
use VarMap\ArrayVarMap;

/**
 * @group db
 * @coversNothing
 */
class PdoChatMessageRepoTest extends ChatMessageRepoFixture
{
//    use DbTransactionIsolation;
    use HasTestWorld;

//    public function setUp(): void
//    {
//        parent::setUp();
//        $this->dbTransactionSetUp();
//    }
//
//    public function tearDown(): void
//    {
//        $this->dbTransactionTearDown();
//        parent::tearDown();
//    }

    protected function dbTransactionClearTables(): void
    {
        $pdoSimple = $this->injector->make(PdoSimple::class);
//        $pdoSimple->execute('DELETE FROM chat_message', []);
    }

    public function getTestInstance(): ChatMessageRepo
    {
        return $this->injector->make(PdoChatMessageRepo::class);
    }

    protected function getTestUserId(): string
    {
        $this->ensureStandardSetup();
        return $this->standardTestData()->getTestingUserId();
    }

    protected function getTestRoomId(): string
    {
        $this->ensureStandardSetup();
        return $this->standardTestData()->getHousingRoom()->id;
    }

    protected function getOtherRoomId(): string
    {
        $this->ensureStandardSetup();
        return $this->standardTestData()->getOffTopicRoom()->id;
    }

    /**
     * @covers \Bristolian\Repo\ChatMessageRepo\PdoChatMessageRepo::__construct
     * @covers \Bristolian\Repo\ChatMessageRepo\PdoChatMessageRepo::storeChatMessageForUser
     */
    public function test_pdo_storeChatMessageForUser_persists_and_returns_message(): void
    {
        $repo = $this->injector->make(PdoChatMessageRepo::class);
        $this->ensureStandardSetup();
        $userId = $this->standardTestData()->getTestingUserId();
        $roomId = $this->standardTestData()->getHousingRoom()->id;

        $param = ChatMessageParam::createFromVarMap(new ArrayVarMap([
            'text' => 'Pdo store test ' . create_test_uniqid(),
            'room_id' => $roomId,
        ]));

        $message = $repo->storeChatMessageForUser($userId, $param);
        $this->assertInstanceOf(UserChatMessage::class, $message);
        $this->assertSame($userId, $message->user_id);
        $this->assertSame($roomId, $message->room_id);
    }

    /**
     * @covers \Bristolian\Repo\ChatMessageRepo\PdoChatMessageRepo::getMessagesForRoom
     */
    public function test_pdo_getMessagesForRoom_returns_only_messages_for_room_ordered_desc(): void
    {
        $repo = $this->injector->make(PdoChatMessageRepo::class);
        $this->ensureStandardSetup();
        $userId = $this->standardTestData()->getTestingUserId();
        $room = $this->world()->roomRepo()->createRoom(
            $userId,
            'chat-' . time() . '-' . random_int(100, 999),
            'Chat message test room'
        );

        $param1 = ChatMessageParam::createFromVarMap(new ArrayVarMap([
            'text' => 'First',
            'room_id' => $room->id,
        ]));
        $param2 = ChatMessageParam::createFromVarMap(new ArrayVarMap([
            'text' => 'Second',
            'room_id' => $room->id,
        ]));

        $repo->storeChatMessageForUser($userId, $param1);
        $repo->storeChatMessageForUser($userId, $param2);

        $messages = $repo->getMessagesForRoom($room->id);
        $this->assertCount(2, $messages);
        $this->assertContainsOnlyInstancesOf(UserChatMessage::class, $messages);
        $this->assertGreaterThanOrEqual($messages[1]->id, $messages[0]->id);
    }
}
