<?php

namespace BristolianTest\Model;

use BristolianTest\BaseTestCase;
use Bristolian\Model\ChatMessage;

/**
 * @coversNothing
 * @group wip
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

    public function testToStringAndBackAgain()
    {
        $chatMessage = new ChatMessage(
            1,
            'user-id',
            'room-id',
            'Test message',
            null,
            new \DateTimeImmutable()
        );
        $string = $chatMessage->toString();

        $recreated_message = ChatMessage::fromString($string);
        
        // Compare all properties except DateTime first
        $this->assertSame($chatMessage->id, $recreated_message->id);
        $this->assertSame($chatMessage->user_id, $recreated_message->user_id);
        $this->assertSame($chatMessage->room_id, $recreated_message->room_id);
        $this->assertSame($chatMessage->text, $recreated_message->text);
        $this->assertSame($chatMessage->message_reply_id, $recreated_message->message_reply_id);
        
        // DateTime comparison: The serialization format may lose microsecond precision,
        // so we compare timestamps (second precision) rather than the objects directly
        $this->assertEquals(
            $chatMessage->created_at->getTimestamp(),
            $recreated_message->created_at->getTimestamp(),
            'DateTime should match at second precision after round-trip'
        );
    }
}

