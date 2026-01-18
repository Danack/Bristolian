<?php

namespace BristolianTest\Model;

use BristolianTest\BaseTestCase;
use Bristolian\Model\Chat\UserChatMessage;

/**
 * @coversNothing
 * @group wip_borken
 */
class ChatMessageTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\Chat\UserChatMessage
     */
    public function testConstruct()
    {
        $id = 123;
        $userId = 'user-456';
        $roomId = 'room-789';
        $text = 'Hello, world!';
        $messageReplyId = 100;
        $createdAt = new \DateTimeImmutable();

        $chatMessage = new UserChatMessage(
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
        $this->assertSame($messageReplyId, $chatMessage->reply_message_id);
        $this->assertSame($createdAt, $chatMessage->created_at);
    }

    /**
     * @covers \Bristolian\Model\Chat\UserChatMessage
     */
    public function testConstructWithNullReplyId()
    {
        $chatMessage = new UserChatMessage(
            1,
            'user-id',
            'room-id',
            'Message text',
            null,
            new \DateTimeImmutable()
        );

        $this->assertNull($chatMessage->reply_message_id);
    }

    /**
     * @covers \Bristolian\Model\Chat\UserChatMessage
     */
    public function testToArray()
    {
        $chatMessage = new UserChatMessage(
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
        $chatMessage = new UserChatMessage(
            1,
            'user-id',
            'room-id',
            'Test message',
            null,
            new \DateTimeImmutable()
        );
        $string = $chatMessage->toString();

        $recreated_message = UserChatMessage::fromString($string);
        
        // Compare all properties except DateTime first
        $this->assertSame($chatMessage->id, $recreated_message->id);
        $this->assertSame($chatMessage->user_id, $recreated_message->user_id);
        $this->assertSame($chatMessage->room_id, $recreated_message->room_id);
        $this->assertSame($chatMessage->text, $recreated_message->text);
        $this->assertSame($chatMessage->reply_message_id, $recreated_message->reply_message_id);
        
        // DateTime comparison: The serialization format may lose microsecond precision,
        // so we compare timestamps (second precision) rather than the objects directly
        $this->assertEquals(
            $chatMessage->created_at->getTimestamp(),
            $recreated_message->created_at->getTimestamp(),
            'DateTime should match at second precision after round-trip'
        );
    }
}
