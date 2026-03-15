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
     * @covers \Bristolian\Repo\ChatMessageRepo\FakeChatMessageRepo::storeChatMessageForUser
     * @covers \Bristolian\Repo\ChatMessageRepo\PdoChatMessageRepo::__construct
     * @covers \Bristolian\Repo\ChatMessageRepo\PdoChatMessageRepo::storeChatMessageForUser
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
     * @covers \Bristolian\Repo\ChatMessageRepo\FakeChatMessageRepo::storeChatMessageForUser
     * @covers \Bristolian\Repo\ChatMessageRepo\PdoChatMessageRepo::storeChatMessageForUser
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
}
