<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\ChatMessageRepo;

use Bristolian\Model\Chat\UserChatMessage;
use Bristolian\Parameters\ChatMessageParam;
use Bristolian\Repo\ChatMessageRepo\ChatMessageRepo;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;
use VarMap\ArrayVarMap;

/**
 * Abstract test class for ChatMessageRepo implementations.
 *
 * @coversNothing
 */
abstract class ChatMessageRepoFixture extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * Get a test instance of the ChatMessageRepo implementation.
     *
     * @return ChatMessageRepo
     */
    abstract public function getTestInstance(): ChatMessageRepo;

    /**
     * Get a test user ID. Override in PDO tests to create actual user.
     */
    protected function getTestUserId(): string
    {
        return 'user-123';
    }

    /**
     * Get a test room ID. Override in PDO tests to create actual room.
     */
    protected function getTestRoomId(): string
    {
        return 'room-456';
    }

    /**
     * Get another room ID (for tests that need a distinct "other" room).
     * Override in PDO tests to return a real second room (e.g. Off-topic).
     */
    protected function getOtherRoomId(): string
    {
        return 'different-room';
    }

    /**
     * @covers \Bristolian\Repo\ChatMessageRepo\ChatMessageRepo::storeChatMessageForUser
     */
    public function test_storeChatMessageForUser_creates_and_returns_message(): void
    {
        $repo = $this->getTestInstance();

        $user_id = $this->getTestUserId();
        $room_id = $this->getTestRoomId();
        $chatMessageParam = ChatMessageParam::createFromVarMap(new ArrayVarMap([
            'text' => 'Hello, world!',
            'room_id' => $room_id,
        ]));

        $message = $repo->storeChatMessageForUser($user_id, $chatMessageParam);

        $this->assertInstanceOf(UserChatMessage::class, $message);
        $this->assertSame($user_id, $message->user_id);
        $this->assertSame($room_id, $message->room_id);
        $this->assertSame('Hello, world!', $message->text);
        $this->assertNull($message->reply_message_id);
    }

    /**
     * @covers \Bristolian\Repo\ChatMessageRepo\ChatMessageRepo::storeChatMessageForUser
     */
    public function test_storeChatMessageForUser_stores_message_with_reply_id(): void
    {
        $repo = $this->getTestInstance();

        $user_id = $this->getTestUserId();
        $chatMessageParam = ChatMessageParam::createFromVarMap(new ArrayVarMap([
            'text' => 'Reply message',
            'room_id' => $this->getTestRoomId(),
            'message_reply_id' => 789,
        ]));

        $message = $repo->storeChatMessageForUser($user_id, $chatMessageParam);

        $this->assertInstanceOf(UserChatMessage::class, $message);
        $this->assertSame(789, $message->reply_message_id);
    }

    /**
     * @covers \Bristolian\Repo\ChatMessageRepo\ChatMessageRepo::getMessagesForRoom
     * @covers \Bristolian\Repo\ChatMessageRepo\ChatMessageRepo::storeChatMessageForUser
     */
    public function test_getMessagesForRoom_returns_messages_for_specific_room(): void
    {
        $repo = $this->getTestInstance();

        $user_id = $this->getTestUserId();
        $room_id = $this->getTestRoomId();
        
        $chatMessageParam1 = ChatMessageParam::createFromVarMap(new ArrayVarMap([
            'text' => 'Message 1',
            'room_id' => $room_id,
        ]));
        $chatMessageParam2 = ChatMessageParam::createFromVarMap(new ArrayVarMap([
            'text' => 'Message 2',
            'room_id' => $room_id,
        ]));
        $chatMessageParam3 = ChatMessageParam::createFromVarMap(new ArrayVarMap([
            'text' => 'Message 3',
            'room_id' => $this->getOtherRoomId(),
        ]));

        $repo->storeChatMessageForUser($user_id, $chatMessageParam1);
        $repo->storeChatMessageForUser($user_id, $chatMessageParam2);
        $repo->storeChatMessageForUser($user_id, $chatMessageParam3);

        $messages = $repo->getMessagesForRoom($room_id);

        $this->assertCount(2, $messages);
        $this->assertContainsOnlyInstancesOf(UserChatMessage::class, $messages);
        foreach ($messages as $message) {
            $this->assertSame($room_id, $message->room_id);
        }
    }

    /**
     * @covers \Bristolian\Repo\ChatMessageRepo\ChatMessageRepo::getMessagesForRoom
     * @covers \Bristolian\Repo\ChatMessageRepo\ChatMessageRepo::storeChatMessageForUser
     */
    public function test_getMessagesForRoom_returns_messages_sorted_by_id_descending(): void
    {
        $repo = $this->getTestInstance();

        $user_id = $this->getTestUserId();
        $room_id = $this->getTestRoomId();
        
        $chatMessageParam1 = ChatMessageParam::createFromVarMap(new ArrayVarMap([
            'text' => 'First message',
            'room_id' => $room_id,
        ]));
        $chatMessageParam2 = ChatMessageParam::createFromVarMap(new ArrayVarMap([
            'text' => 'Second message',
            'room_id' => $room_id,
        ]));
        $chatMessageParam3 = ChatMessageParam::createFromVarMap(new ArrayVarMap([
            'text' => 'Third message',
            'room_id' => $room_id,
        ]));

        $message1 = $repo->storeChatMessageForUser($user_id, $chatMessageParam1);
        $message2 = $repo->storeChatMessageForUser($user_id, $chatMessageParam2);
        $message3 = $repo->storeChatMessageForUser($user_id, $chatMessageParam3);

        $messages = $repo->getMessagesForRoom($room_id);

        $this->assertCount(3, $messages);
        // Messages should be sorted by id descending (newest first)
        $this->assertGreaterThanOrEqual($messages[1]->id, $messages[0]->id);
        $this->assertGreaterThanOrEqual($messages[2]->id, $messages[1]->id);
    }
}
