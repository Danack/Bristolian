<?php

namespace BristolianTest\Model;

use BristolianTest\BaseTestCase;
use Bristolian\Model\ChatMessage;

/**
 * @coversNothing
 */
class ChatMessageTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\ChatMessage
     */
    public function testConstruct()
    {
        $id = 123;
        $userId = 'user-456';
        $roomId = 'room-789';
        $text = 'Hello, world!';
        $messageReplyId = 100;
        $createdAt = new \DateTimeImmutable();

        $chatMessage = new ChatMessage(
            $id,
            $userId,
            $roomId,
            $text,
            $messageReplyId,
            $createdAt
        );

        $this->assertSame($id, $chatMessage->id);
        $this->assertSame($userId, $chatMessage->user_id);
        $this->assertSame($roomId, $chatMessage->room_id);
        $this->assertSame($text, $chatMessage->text);
        $this->assertSame($messageReplyId, $chatMessage->message_reply_id);
        $this->assertSame($createdAt, $chatMessage->created_at);
    }

    /**
     * @covers \Bristolian\Model\ChatMessage
     */
    public function testConstructWithNullReplyId()
    {
        $chatMessage = new ChatMessage(
            1,
            'user-id',
            'room-id',
            'Message text',
            null,
            new \DateTimeImmutable()
        );

        $this->assertNull($chatMessage->message_reply_id);
    }

    /**
     * @covers \Bristolian\Model\ChatMessage
     */
    public function testToArray()
    {
        $chatMessage = new ChatMessage(
            1,
            'user-id',
            'room-id',
            'Test message',
            null,
            new \DateTimeImmutable()
        );

        $array = $chatMessage->toArray();
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('text', $array);
    }
}

