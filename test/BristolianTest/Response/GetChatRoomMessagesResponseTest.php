<?php

namespace BristolianTest\Response;

use Bristolian\Model\Chat\UserChatMessage;
use Bristolian\Response\GetChatRoomMessagesResponse;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Response\GetChatRoomMessagesResponse
 */
class GetChatRoomMessagesResponseTest extends BaseTestCase
{
    private static function message(int $id, string $text, string $userId = 'user-1', string $roomId = 'room-1', ?int $replyMessageId = null): UserChatMessage
    {
        return new UserChatMessage(
            $id,
            $userId,
            $roomId,
            $text,
            $replyMessageId,
            new \DateTimeImmutable('2025-01-01 12:00:00')
        );
    }

    public function testGetStatusReturns200(): void
    {
        $messages = [
            self::message(1, 'Hello', 'user-1'),
            self::message(2, 'World', 'user-2'),
        ];
        $response = new GetChatRoomMessagesResponse($messages);

        $this->assertSame(200, $response->getStatus());
    }

    public function testGetHeadersReturnsContentType(): void
    {
        $messages = [self::message(1, 'Hello')];
        $response = new GetChatRoomMessagesResponse($messages);
        $headers = $response->getHeaders();

        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    public function testGetBodyReturnsMessages(): void
    {
        $messages = [
            self::message(1, 'Hello', 'user-1'),
            self::message(2, 'World', 'user-2'),
        ];
        $response = new GetChatRoomMessagesResponse($messages);
        $body = $response->getBody();

        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertSame('success', $decoded['result']);
        $this->assertArrayHasKey('data', $decoded);
        $this->assertArrayHasKey('messages', $decoded['data']);
        $this->assertCount(2, $decoded['data']['messages']);
    }

    public function testGetBodyWithEmptyMessages(): void
    {
        $messages = [];
        $response = new GetChatRoomMessagesResponse($messages);
        $body = $response->getBody();
        
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertSame('success', $decoded['result']);
        $this->assertCount(0, $decoded['data']['messages']);
    }
}
