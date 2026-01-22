<?php

namespace BristolianTest\Response;

use Bristolian\Exception\DataEncodingException;
use Bristolian\Response\GetChatRoomMessagesResponse;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Response\GetChatRoomMessagesResponse
 */
class GetChatRoomMessagesResponseTest extends BaseTestCase
{
    public function testGetStatusReturns200()
    {
        $messages = [
            ['id' => 1, 'text' => 'Hello', 'user_id' => 'user-1'],
            ['id' => 2, 'text' => 'World', 'user_id' => 'user-2']
        ];
        $response = new GetChatRoomMessagesResponse($messages);
        
        $this->assertSame(200, $response->getStatus());
    }

    public function testGetHeadersReturnsContentType()
    {
        $messages = [['id' => 1, 'text' => 'Hello']];
        $response = new GetChatRoomMessagesResponse($messages);
        $headers = $response->getHeaders();
        
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    public function testGetBodyReturnsMessages()
    {
        $messages = [
            ['id' => 1, 'text' => 'Hello', 'user_id' => 'user-1'],
            ['id' => 2, 'text' => 'World', 'user_id' => 'user-2']
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

    public function testGetBodyWithEmptyMessages()
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
