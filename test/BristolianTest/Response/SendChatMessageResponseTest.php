<?php

namespace BristolianTest\Response;

use Bristolian\Exception\DataEncodingException;
use Bristolian\Model\Chat\UserChatMessage;
use Bristolian\Response\SendChatMessageResponse;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Response\SendChatMessageResponse
 */
class SendChatMessageResponseTest extends BaseTestCase
{
    public function testGetStatusReturns200()
    {
        $chatMessage = new UserChatMessage(
            id: 1,
            user_id: 'user-123',
            room_id: 'room-456',
            text: 'Hello world',
            reply_message_id: null,
            created_at: new \DateTimeImmutable()
        );
        $response = new SendChatMessageResponse($chatMessage);
        
        $this->assertSame(200, $response->getStatus());
    }

    public function testGetHeadersReturnsContentType()
    {
        $chatMessage = new UserChatMessage(
            id: 1,
            user_id: 'user-123',
            room_id: 'room-456',
            text: 'Hello world',
            reply_message_id: null,
            created_at: new \DateTimeImmutable()
        );
        $response = new SendChatMessageResponse($chatMessage);
        $headers = $response->getHeaders();
        
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    public function testGetBodyReturnsChatMessage()
    {
        $createdAt = new \DateTimeImmutable('2024-01-15 12:00:00');
        $chatMessage = new UserChatMessage(
            id: 1,
            user_id: 'user-123',
            room_id: 'room-456',
            text: 'Hello world',
            reply_message_id: null,
            created_at: $createdAt
        );
        $response = new SendChatMessageResponse($chatMessage);
        $body = $response->getBody();
        
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertSame('success', $decoded['result']);
        $this->assertArrayHasKey('data', $decoded);
        $this->assertArrayHasKey('chat_message', $decoded['data']);
        $this->assertSame(1, $decoded['data']['chat_message']['id']);
        $this->assertSame('Hello world', $decoded['data']['chat_message']['text']);
    }

    public function testGetBodyWithReplyMessageId()
    {
        $createdAt = new \DateTimeImmutable('2024-01-15 12:00:00');
        $chatMessage = new UserChatMessage(
            id: 2,
            user_id: 'user-123',
            room_id: 'room-456',
            text: 'Reply message',
            reply_message_id: 1,
            created_at: $createdAt
        );
        $response = new SendChatMessageResponse($chatMessage);
        $body = $response->getBody();
        
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertSame(1, $decoded['data']['chat_message']['reply_message_id']);
    }

    public function testThrowsExceptionWithNonUtf8Text()
    {
        // Create a string with invalid UTF-8 bytes (0xFF 0xFE is not valid UTF-8)
        $invalidUtf8Text = "\xFF\xFEInvalid UTF-8 sequence";
        
        $chatMessage = new UserChatMessage(
            id: 1,
            user_id: 'user-123',
            room_id: 'room-456',
            text: $invalidUtf8Text,
            reply_message_id: null,
            created_at: new \DateTimeImmutable()
        );
        
        // This will throw JsonException at json_encode_safe, not DataEncodingException
        // The error path at line 19 requires convertToValue to return an error,
        // which is difficult to achieve with UserChatMessage since it uses toArray()
        // which throws exceptions rather than returning errors
        $this->expectException(\Bristolian\Exception\JsonException::class);
        
        new SendChatMessageResponse($chatMessage);
    }
}
